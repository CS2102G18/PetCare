CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE pets_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE request_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE avail_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE assn_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE pcat_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;

CREATE TABLE petcategory(
    pcat_id INT PRIMARY KEY DEFAULT nextval('pcat_seq'),
    age VARCHAR(10),
    size VARCHAR(20),
    species VARCHAR(30),
    UNIQUE (age, size, species)
);

CREATE TABLE pet_user(
    user_id INT PRIMARY KEY DEFAULT nextval('user_id_seq'),
    name VARCHAR(64) NOT NULL,
    password VARCHAR(64) NOT NULL,
    email VARCHAR(64) UNIQUE,
    address VARCHAR(64),
    role VARCHAR(10) DEFAULT 'normal' CONSTRAINT CHK_role CHECK (role in ('admin', 'normal')),
    is_deleted BOOLEAN DEFAULT FALSE
);

CREATE TABLE pet(
    pets_id INT PRIMARY KEY DEFAULT nextval('pets_id_seq'),
    owner_id INT REFERENCES pet_user(user_id) ON DELETE CASCADE,
    pcat_id INT REFERENCES petcategory(pcat_id) ON DELETE CASCADE ON UPDATE CASCADE,
    pet_name VARCHAR(64),
    is_deleted BOOLEAN DEFAULT FALSE,
    UNIQUE (owner_id, pet_name)
);

CREATE TABLE availability(
    avail_id INT PRIMARY KEY DEFAULT nextval('avail_id_seq'),
    post_time timestamp NOT NULL DEFAULT current_timestamp,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    pcat_id INT REFERENCES petcategory(pcat_id) ON DELETE CASCADE ON UPDATE CASCADE,
    taker_id INT REFERENCES pet_user(user_id) ON DELETE CASCADE,
    remarks VARCHAR(64) DEFAULT 'No',
    is_deleted BOOLEAN DEFAULT FALSE,
    UNIQUE (start_time, end_time, pcat_id, taker_id),
    CONSTRAINT CHK_start_end CHECK (end_time > start_time),
    CONSTRAINT CHK_post CHECK (start_time > post_time)
);

CREATE TABLE request(
    request_id INT PRIMARY KEY DEFAULT nextval('request_id_seq'),
    owner_id INT REFERENCES pet_user(user_id) ON DELETE CASCADE,
    taker_id INT REFERENCES pet_user(user_id) ON DELETE CASCADE,
    post_time TIMESTAMP NOT NULL DEFAULT current_timestamp,
    care_begin TIMESTAMP NOT NULL,
    care_end TIMESTAMP NOT NULL,
    remarks VARCHAR(64) DEFAULT 'No',
    bids NUMERIC NOT NULL,
    pets_id INT REFERENCES pet(pets_id) ON DELETE CASCADE ON UPDATE CASCADE,
    slot VARCHAR(64),
    totaltime DOUBLE PRECISION,
    status VARCHAR(20) CHECK (status IN ('pending', 'failed', 'successful', 'cancelled')) DEFAULT 'pending',
    CONSTRAINT CHK_start_end CHECK (care_end > care_begin),
    CONSTRAINT CHK_post CHECK (care_begin > post_time)
);


CREATE OR REPLACE FUNCTION timeslot(requestNum INTEGER)
RETURNS VARCHAR(64) AS $$
DECLARE slot VARCHAR(64); hours DOUBLE PRECISION; beginTime timestamp;
BEGIN
SELECT care_begin INTO beginTime FROM request WHERE request_id = requestNum;
hours = extract(HOUR FROM (beginTime));
IF hours BETWEEN 6 AND 11 THEN slot = 'Morning';
ELSE IF hours BETWEEN 12 AND 17 THEN slot = 'Afternoon';
ELSE IF hours BETWEEN 18 AND 23 THEN slot = 'Evening';
ELSE slot = 'Before Dawn';
END IF;
END IF;
END IF;
RETURN slot;
END; $$
LANGUAGE PLPGSQL;

CREATE OR REPLACE FUNCTION calculateTotalTime(requestNum INTEGER)
RETURNS DOUBLE PRECISION AS $$
DECLARE totalmins DOUBLE PRECISION; days DOUBLE PRECISION; hours DOUBLE PRECISION; mins DOUBLE PRECISION;
startTime timestamp; endTime timestamp;
BEGIN
SELECT care_begin, care_end INTO startTime, endTime FROM request WHERE request_id = requestNum;
mins = extract(MINUTE FROM (endTime - startTime));
days = extract(DAY FROM (endTime - startTime));
hours = extract(HOUR FROM (endTime - startTime));
totalmins = mins + 60 * (hours + 24 * days);
RETURN totalmins;
END; $$
LANGUAGE PLPGSQL;

CREATE OR REPLACE FUNCTION addRequestInfo()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE request
    SET slot= timeslot(new.request_id), totaltime = calculateTotalTime(new.request_id)
    WHERE request_id = new.request_id;
    RETURN NULL;
END; $$
LANGUAGE PLPGSQL;


CREATE TRIGGER addSlot
AFTER INSERT
ON request
FOR EACH ROW
EXECUTE PROCEDURE addRequestInfo();

CREATE OR REPLACE FUNCTION cleanOutdatedAvail()
RETURNS TRIGGER AS $$
BEGIN
  UPDATE availability
  SET is_deleted = TRUE
  WHERE end_time <= CURRENT_TIMESTAMP
  and is_deleted = FALSE;
  RETURN NULL;
END; $$
LANGUAGE PLPGSQL;

CREATE OR REPLACE FUNCTION cleanOutdatedReq()
RETURNS TRIGGER AS $$
BEGIN
  UPDATE request
  SET status = 'cancelled'
  WHERE (care_begin <= CURRENT_TIMESTAMP
  AND status = 'pending')
  OR
  (request_id NOT IN (SELECT r.request_id
                      FROM request r INNER JOIN pet p ON r.pets_id = p.pets_id
                                     INNER JOIN availability a ON a.pcat_id = p.pcat_id
                      WHERE r.taker_id = a.taker_id
                      AND a.is_deleted = FALSE
                      AND p.is_deleted = FALSE
                      AND r.care_end <= a.end_time
                      AND r.care_begin >= a.start_time)
  AND status = 'pending');
  RETURN NULL;
END; $$
LANGUAGE PLPGSQL;

CREATE TRIGGER changeAvail
AFTER INSERT ON availability
FOR EACH STATEMENT
EXECUTE PROCEDURE cleanOutdatedAvail();

CREATE TRIGGER changeReq
AFTER INSERT ON request
FOR EACH STATEMENT
EXECUTE PROCEDURE cleanOutdatedReq();


CREATE VIEW requesttime AS
    SELECT SUM(r.bids)/SUM(r.totaltime)*60 AS avgbids, r.taker_id AS taker_id
    FROM request r
    WHERE r.status = 'successful'
    GROUP BY r.taker_id;