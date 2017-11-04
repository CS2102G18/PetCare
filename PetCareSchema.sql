DROP VIEW requesttime;
DROP TABLE request;
DROP TABLE availability;
DROP TABLE pet;
DROP TABLE petcategory;
DROP TABLE pet_user;

DROP SEQUENCE user_id_seq;
DROP SEQUENCE pets_id_seq;
DROP SEQUENCE request_id_seq;
DROP SEQUENCE avail_id_seq;
DROP SEQUENCE assn_id_seq;
DROP SEQUENCE pcat_seq;

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
    species VARCHAR(30)
);

INSERT INTO petcategory (age, size, species) VALUES ('puppy','small','cat');
INSERT INTO petcategory (age, size, species) VALUES ('puppy','small','dog');
INSERT INTO petcategory (age, size, species) VALUES ('puppy','small','rabbit');
INSERT INTO petcategory (age, size, species) VALUES ('puppy','medium','cat');
INSERT INTO petcategory (age, size, species) VALUES ('puppy','medium','dog');
INSERT INTO petcategory (age, size, species) VALUES ('puppy','medium','rabbit');
INSERT INTO petcategory (age, size, species) VALUES ('puppy','large','cat');
INSERT INTO petcategory (age, size, species) VALUES ('puppy','large','dog');
INSERT INTO petcategory (age, size, species) VALUES ('puppy','large','rabbit');
INSERT INTO petcategory (age, size, species) VALUES ('puppy','giant','cat');
INSERT INTO petcategory (age, size, species) VALUES ('puppy','giant','dog');
INSERT INTO petcategory (age, size, species) VALUES ('puppy','giant','rabbit');
INSERT INTO petcategory (age, size, species) VALUES ('adult','small','cat');
INSERT INTO petcategory (age, size, species) VALUES ('adult','small','dog');
INSERT INTO petcategory (age, size, species) VALUES ('adult','small','rabbit');
INSERT INTO petcategory (age, size, species) VALUES ('adult','medium','cat');
INSERT INTO petcategory (age, size, species) VALUES ('adult','medium','dog');
INSERT INTO petcategory (age, size, species) VALUES ('adult','medium','rabbit');
INSERT INTO petcategory (age, size, species) VALUES ('adult','large','cat');
INSERT INTO petcategory (age, size, species) VALUES ('adult','large','dog');
INSERT INTO petcategory (age, size, species) VALUES ('adult','large','rabbit');
INSERT INTO petcategory (age, size, species) VALUES ('adult','giant','cat');
INSERT INTO petcategory (age, size, species) VALUES ('adult','giant','dog');
INSERT INTO petcategory (age, size, species) VALUES ('adult','giant','rabbit');

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
  and is_deleted = FALSE
  RETURN NULL;
END; $$
LANGUAGE PLPGSQL

CREATE OR REPLACE FUNCTION cleanOutdatedReq()
RETURNS TRIGGER AS $$
BEGIN
  UPDATE request
  SET status = 'cancelled'
  WHERE (end_time <= CURRENT_TIMESTAMP
  AND status = 'pending')
  OR
  (request_id NOT IN (SELECT r.request_id
                      FROM request r INNER JOIN pet p ON r.pets_id = p.pets_id
                                     INNER JOIN availability a ON a.pcat_id = p.pcat_id
                      WHERE r.taker_id = a.taker_id
                      AND a.is_deleted = FALSE
                      AND p.is_deleted = FALSE
                      AND r.care_end <= a.end_time
                      AND r.care_begin >= a.begin_time)
  AND status = 'pending')
  RETURN NULL;
END; $$
LANGUAGE PLPGSQL

CREATE TRIGGER changeAvail
BEFORE INSERT OR UPDATE availability
FOR EACH STATEMENT
EXECUTE PROCEDURE cleanOutdatedAvail();

CREATE TRIGGER changeReq
BEFORE INSERT OR UPDATE request
FOR EACH STATEMENT
EXECUTE PROCEDURE cleanOutdatedReq();


CREATE VIEW requesttime AS
    SELECT SUM(r.bids)/SUM(r.totaltime)*60 AS avgbids, r.taker_id AS taker_id
    FROM request r
    WHERE r.status = 'successful'
    GROUP BY r.taker_id;


INSERT INTO pet_user(name, password, email, address, role) VALUES ('Xia Rui',12345,'e0012672@u.nus.edu','30 Ang Mo Kio Ave 8', 'admin');
INSERT INTO pet_user(name, password, email, address, role) VALUES ('Chen Penghao',12345,'e0004801@u.nus.edu','33 Lorong 2 Toa Payoh', 'admin');
INSERT INTO pet_user(name, password, email, address, role) VALUES ('Xie Peiyi',12345,'peiyi@u.nus.edu','55 Hougang Ave 10', 'admin');
INSERT INTO pet_user(name, password, email, address, role) VALUES ('Kuang Ming',12345,'km@msn.com','165 Tampines Ave 10', 'admin');

INSERT INTO pet_user(name, password, email, address) VALUES ('Patti Dennis',12345,'empathy@msn.com','157 Foxrun Street Newnan, GA 30263');
INSERT INTO pet_user(name, password, email, address) VALUES ('Carmen Grant',23456,'presoff@hotmail.com','9 South Surrey Street Rockford, MI 49341');
INSERT INTO pet_user(name, password, email, address) VALUES ('Abel Lucas',34567,'keijser@optonline.net','930 Storm Court Washington, PA 15301');
INSERT INTO pet_user(name, password, email, address) VALUES ('Marguerite Jennings',45678,'curly@gmail.com','508 E. Longfellow Rd. Revere, MA 02151');
INSERT INTO pet_user(name, password, email, address) VALUES ('Samuel Lawrence',56789,'squirrel@aol.com','8807 Aurora Road Ogden, UT 84404');
INSERT INTO pet_user(name, password, email, address) VALUES ('Lydia Turner',67900,'cantu@verizon.net','29 Paradise Court Moorhead, MN 56560');
INSERT INTO pet_user(name, password, email, address) VALUES ('Eloise Cooper',79011,'pajas@msn.com','9267 1st St. Wenatchee, WA 98801');
INSERT INTO pet_user(name, password, email, address) VALUES ('Maxine Ramos',90122,'vertigo@aol.com','671 Liberty Dr. Ankeny, IA 50023');
INSERT INTO pet_user(name, password, email, address) VALUES ('Kyle Colon',12334,'aprakash@me.com','49 Walt Whitman Street Apopka, FL 32703');
INSERT INTO pet_user(name, password, email, address) VALUES ('Laverne Valdez',12344,'lishoy@verizon.net','12 Bald Hill Street Norfolk, VA 23503');
INSERT INTO pet_user(name, password, email, address) VALUES ('David Reynolds',23455,'marnanel@hotmail.com','224 Second Drive Cocoa, FL 32927');
INSERT INTO pet_user(name, password, email, address) VALUES ('Clyde Mack',34566,'smartfart@verizon.net','870 Addison Court Dacula, GA 30019');
INSERT INTO pet_user(name, password, email, address) VALUES ('Cameron Huff',45677,'petersko@yahoo.ca','7834 Ann Street Quincy, MA 02169');
INSERT INTO pet_user(name, password, email, address) VALUES ('Ebony Mendez',56788,'avalon@att.net','8789 Hart St. Ballston Spa, NY 12020');
INSERT INTO pet_user(name, password, email, address) VALUES ('Joe Munoz',67899,'ournews@live.com','94 Meadowbrook St.Apt 36 Florence, SC 29501');
INSERT INTO pet_user(name, password, email, address) VALUES ('Travis Pearson',79010,'chaffar@mac.com','436 E. Second Avenue Missoula, MT 59801');
INSERT INTO pet_user(name, password, email, address) VALUES ('Robin Goodman',90121,'mdielmann@hotmail.com','11 Brewer Road Chardon, OH 44024');
INSERT INTO pet_user(name, password, email, address) VALUES ('Marcus Gilbert',81232,'weazelman@yahoo.com','12 Summerhouse St. Hoboken, NJ 07030');
INSERT INTO pet_user(name, password, email, address) VALUES ('Doug Neal',12343,'msloan@me.com','5 East Proctor Street Missoula, MT 59801');
INSERT INTO pet_user(name, password, email, address) VALUES ('Josephine Erickson',23454,'goresky@msn.com','7943 East Lakeshore Street Rockford, MI 49341');

/*
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (2,1,'Ah Beng');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (3,5,'Ah Lian');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (5,4,'Ah Hong');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (1,2,'Ah Ben');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (4,8,'Ah Wong');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (7,3,'Ah Kay');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (2,9,'Ah Seng');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (5,6,'Ah Leong');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (2,10,'Ah Mah');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (8,7,'Ah Wai');
*/

/*
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 1, 1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 2, 1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 3, 1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 4, 1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 5, 1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 6, 2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 1, 2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 2, 2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 3, 2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 4, 2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 5, 3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 6, 3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 1, 3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 2, 3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 3, 3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 4, 4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 5, 4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 6, 4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 1, 4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 2, 4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 3, 5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 4, 5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2017-11-01 08:00:00', '2017-12-01 08:00:00', 5, 5);
*/

INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (21,1,'1');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (17,1,'2');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (6,2,'3');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (7,2,'4');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (24,3,'5');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (12,3,'6');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (4,4,'7');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (13,4,'8');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (15,5,'9');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (13,5,'10');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (13,6,'11');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (22,6,'12');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (9,7,'13');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (22,7,'14');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (12,8,'15');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (19,8,'16');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (18,9,'17');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (14,9,'18');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (10,10,'19');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (19,10,'20');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (4,11,'21');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (22,11,'22');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (4,12,'23');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (4,12,'24');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (5,13,'25');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (5,13,'26');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (21,14,'27');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (13,14,'28');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (1,15,'29');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (2,15,'30');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (2,16,'31');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (8,16,'32');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (3,17,'33');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (13,17,'34');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (11,18,'35');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (9,18,'36');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (6,19,'37');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (18,19,'38');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (1,20,'39');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (18,20,'40');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (6,21,'41');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (24,21,'42');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (20,22,'43');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (4,22,'44');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (21,23,'45');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (10,23,'46');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (8,24,'47');
INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES (17,24,'48');

INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 01:00:00',3,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-01-21 20:00:00',3,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 01:00:00',3,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 19:00:00',3,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 23:00:00',3,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 01:00:00',9,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 01:00:00',9,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 00:00:00',9,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 13:00:00',9,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 21:00:00',9,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 10:00:00',13,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 23:00:00',13,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 19:00:00',13,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 11:00:00',13,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 12:00:00',13,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 17:00:00',19,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 20:00:00',19,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 00:00:00',19,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 09:00:00',19,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 23:00:00',19,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 05:00:00',20,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 03:00:00',20,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 11:00:00',20,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 07:00:00',20,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 19:00:00',20,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 07:00:00',24,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 00:00:00',24,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 04:00:00',24,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 05:00:00',24,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 14:00:00',24,1);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 05:00:00',3,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 15:00:00',3,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 05:00:00',3,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 08:00:00',3,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 21:00:00',3,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 12:00:00',7,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 11:00:00',7,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 19:00:00',7,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 06:00:00',7,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 17:00:00',7,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 04:00:00',9,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 12:00:00',9,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 04:00:00',9,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 08:00:00',9,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 18:00:00',9,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 13:00:00',10,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 11:00:00',10,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 03:00:00',10,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-01-21 20:00:00',10,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 13:00:00',10,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 09:00:00',20,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 22:00:00',20,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 12:00:00',20,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 19:00:00',20,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 05:00:00',22,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 09:00:00',22,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 11:00:00',22,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 16:00:00',22,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 22:00:00',22,2);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 05:00:00',1,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 08:00:00',1,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 18:00:00',1,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 16:00:00',1,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 20:00:00',1,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 11:00:00',4,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 08:00:00',4,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 01:00:00',4,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 14:00:00',4,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 19:00:00',4,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 15:00:00',9,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 00:00:00',9,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-01-20 20:00:00',9,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 20:00:00',9,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 21:00:00',9,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 17:00:00',14,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 00:00:00',14,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 18:00:00',14,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 15:00:00',14,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 22:00:00',14,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 06:00:00',16,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 18:00:00',16,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 06:00:00',16,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 11:00:00',16,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 20:00:00',16,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 02:00:00',19,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 04:00:00',19,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 17:00:00',19,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 09:00:00',19,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 22:00:00',19,3);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 03:00:00',3,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 11:00:00',3,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 12:00:00',3,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 10:00:00',3,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 22:00:00',3,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 20:00:00',6,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 08:00:00',6,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 23:00:00',6,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 13:00:00',6,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 23:00:00',6,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 14:00:00',11,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 19:00:00',11,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 03:00:00',11,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 14:00:00',11,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 23:00:00',11,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 15:00:00',13,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 16:00:00',13,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 03:00:00',13,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 11:00:00',13,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 21:00:00',13,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 13:00:00',21,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 15:00:00',21,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 12:00:00',21,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 17:00:00',21,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 14:00:00',21,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 04:00:00',23,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 12:00:00',23,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 06:00:00',23,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 08:00:00',23,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 23:00:00',23,4);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 05:00:00',1,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 21:00:00',1,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 05:00:00',1,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 09:00:00',1,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 22:00:00',1,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 14:00:00',2,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 10:00:00',2,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 11:00:00',2,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 20:00:00',2,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 21:00:00',2,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 12:00:00',14,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 00:00:00',14,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 17:00:00',14,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 08:00:00',14,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 23:00:00',14,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 01:00:00',15,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 14:00:00',15,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 00:00:00',15,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 17:00:00',15,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 23:00:00',15,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 10:00:00',16,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 21:00:00',16,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 05:00:00',16,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 13:00:00',16,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 15:00:00',16,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 11:00:00',20,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 08:00:00',20,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 04:00:00',20,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 15:00:00',20,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 16:00:00',20,5);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 03:00:00',1,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 19:00:00',1,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 18:00:00',1,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 21:00:00',1,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 07:00:00',1,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 03:00:00',7,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 15:00:00',7,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 00:00:00',7,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 09:00:00',7,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 20:00:00',7,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 09:00:00',10,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 18:00:00',10,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 02:00:00',10,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 07:00:00',10,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 20:00:00',10,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 06:00:00',13,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 19:00:00',13,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 19:00:00',13,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 09:00:00',13,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 16:00:00',13,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 04:00:00',16,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 20:00:00',16,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 02:00:00',16,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 14:00:00',16,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 18:00:00',16,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 07:00:00',24,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 09:00:00',24,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 03:00:00',24,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 20:00:00',24,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 17:00:00',24,6);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 11:00:00',2,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 09:00:00',2,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 10:00:00',2,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 15:00:00',2,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 18:00:00',2,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 20:00:00',4,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 09:00:00',4,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 18:00:00',4,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 11:00:00',4,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 21:00:00',4,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 03:00:00',7,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 18:00:00',7,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 20:00:00',7,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 13:00:00',7,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 19:00:00',7,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 03:00:00',17,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 13:00:00',17,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 16:00:00',17,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 14:00:00',17,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 21:00:00',17,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 13:00:00',19,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 14:00:00',19,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 13:00:00',19,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 10:00:00',19,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 23:00:00',19,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 12:00:00',24,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 16:00:00',24,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 06:00:00',24,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 02:00:00',24,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 23:00:00',24,7);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 06:00:00',4,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 10:00:00',4,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 02:00:00',4,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 05:00:00',4,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 15:00:00',4,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 18:00:00',9,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 02:00:00',9,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 06:00:00',9,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 07:00:00',9,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 18:00:00',9,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 09:00:00',12,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 13:00:00',12,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 18:00:00',12,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 20:00:00',12,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 22:00:00',12,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 08:00:00',15,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 19:00:00',15,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 23:00:00',15,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 17:00:00',15,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 07:00:00',20,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 15:00:00',20,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 00:00:00',20,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 17:00:00',20,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 22:00:00',20,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 08:00:00',22,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 12:00:00',22,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 23:00:00',22,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 19:00:00',22,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 23:00:00',22,8);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 04:00:00',1,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 10:00:00',1,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 17:00:00',1,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 09:00:00',1,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 10:00:00',1,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 07:00:00',12,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 11:00:00',12,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 11:00:00',12,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 17:00:00',12,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 23:00:00',12,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 03:00:00',16,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 12:00:00',16,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 06:00:00',16,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 05:00:00',16,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 23:00:00',16,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 10:00:00',18,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 01:00:00',18,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 21:00:00',18,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 11:00:00',18,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 23:00:00',18,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 07:00:00',19,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 17:00:00',19,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 04:00:00',19,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 12:00:00',19,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 02:00:00',19,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 04:00:00',22,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 18:00:00',22,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 09:00:00',22,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 07:00:00',22,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 22:00:00',22,9);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 13:00:00',10,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 13:00:00',10,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 00:00:00',10,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 05:00:00',10,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 17:00:00',10,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 03:00:00',15,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 11:00:00',15,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 09:00:00',15,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 08:00:00',15,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 07:00:00',15,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 21:00:00',19,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 20:00:00',19,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 13:00:00',19,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 14:00:00',19,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 10:00:00',21,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 08:00:00',21,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 14:00:00',21,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 06:00:00',21,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 18:00:00',21,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 05:00:00',23,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 15:00:00',23,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 07:00:00',23,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 03:00:00',23,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 21:00:00',23,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 08:00:00',24,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 23:00:00',24,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 06:00:00',24,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 03:00:00',24,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 23:00:00',24,10);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 05:00:00',3,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 18:00:00',3,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 01:00:00',3,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 08:00:00',3,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 23:00:00',3,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 07:00:00',13,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 11:00:00',13,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 06:00:00',13,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 10:00:00',13,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 19:00:00',13,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 11:00:00',16,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 10:00:00',16,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 15:00:00',16,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 05:00:00',16,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 20:00:00',16,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 12:00:00',17,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 12:00:00',17,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 05:00:00',17,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 09:00:00',17,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 21:00:00',17,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 16:00:00',20,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 15:00:00',20,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 00:00:00',20,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 10:00:00',20,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 19:00:00',20,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 14:00:00',22,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 03:00:00',22,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 23:00:00',22,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 10:00:00',22,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 22:00:00',22,11);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 02:00:00',5,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 17:00:00',5,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 05:00:00',5,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 13:00:00',5,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 11:00:00',5,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 12:00:00',11,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 19:00:00',11,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 10:00:00',11,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 20:00:00',11,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 12:00:00',16,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 06:00:00',16,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 16:00:00',16,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 05:00:00',16,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 23:00:00',16,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 11:00:00',18,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 22:00:00',18,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 10:00:00',18,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 11:00:00',18,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 23:00:00',18,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 22:00:00',19,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 14:00:00',19,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 04:00:00',19,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 14:00:00',19,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 20:00:00',19,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 15:00:00',20,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 14:00:00',20,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 23:00:00',20,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 03:00:00',20,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 19:00:00',20,12);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 10:00:00',3,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 12:00:00',3,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 03:00:00',3,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 13:00:00',3,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 22:00:00',3,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 09:00:00',11,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 18:00:00',11,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 08:00:00',11,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 11:00:00',11,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 21:00:00',11,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 05:00:00',14,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 09:00:00',14,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 22:00:00',14,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 13:00:00',14,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 18:00:00',14,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 07:00:00',17,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 09:00:00',17,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 13:00:00',17,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 20:00:00',17,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 06:00:00',22,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 20:00:00',22,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 05:00:00',22,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 08:00:00',22,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 18:00:00',22,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 16:00:00',24,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 18:00:00',24,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 00:00:00',24,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 10:00:00',24,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 12:00:00',24,13);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 14:00:00',3,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 22:00:00',3,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 16:00:00',3,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 04:00:00',3,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 21:00:00',3,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 04:00:00',4,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 15:00:00',4,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 05:00:00',4,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 19:00:00',4,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 20:00:00',4,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 04:00:00',8,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 20:00:00',8,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 03:00:00',8,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 11:00:00',8,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 20:00:00',8,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 14:00:00',10,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 20:00:00',10,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 22:00:00',10,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 08:00:00',10,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 14:00:00',10,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 11:00:00',15,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 06:00:00',15,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 01:00:00',15,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 15:00:00',15,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 14:00:00',15,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 08:00:00',16,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 22:00:00',16,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 23:00:00',16,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 06:00:00',16,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 21:00:00',16,14);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 18:00:00',2,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 00:00:00',2,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 02:00:00',2,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 07:00:00',2,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 20:00:00',2,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 08:00:00',4,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 14:00:00',4,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 02:00:00',4,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 11:00:00',4,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 19:00:00',4,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 13:00:00',9,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 21:00:00',9,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 23:00:00',9,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 11:00:00',9,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 20:00:00',9,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 05:00:00',10,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 15:00:00',10,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 07:00:00',10,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 16:00:00',10,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 17:00:00',10,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 03:00:00',14,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 18:00:00',14,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 21:00:00',14,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 05:00:00',14,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 22:00:00',14,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 08:00:00',22,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 07:00:00',22,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 18:00:00',22,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 18:00:00',22,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 22:00:00',22,15);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 04:00:00',5,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 15:00:00',5,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 02:00:00',5,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 14:00:00',5,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 18:00:00',5,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 05:00:00',7,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 03:00:00',7,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 04:00:00',7,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 15:00:00',7,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 16:00:00',7,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 02:00:00',9,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 17:00:00',9,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 03:00:00',9,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 03:00:00',9,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 23:00:00',9,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 01:00:00',14,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 18:00:00',14,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 01:00:00',14,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 18:00:00',14,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 19:00:00',14,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 11:00:00',16,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 06:00:00',16,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 09:00:00',16,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 18:00:00',16,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 17:00:00',16,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 12:00:00',21,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 20:00:00',21,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 04:00:00',21,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 13:00:00',21,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 22:00:00',21,16);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 05:00:00',1,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 12:00:00',1,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 05:00:00',1,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 08:00:00',1,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 23:00:00',1,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 08:00:00',4,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 17:00:00',4,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 20:00:00',4,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 11:00:00',4,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 23:00:00',4,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 09:00:00',11,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 10:00:00',11,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 09:00:00',11,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 20:00:00',11,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 13:00:00',11,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 03:00:00',14,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 07:00:00',14,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 02:00:00',14,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 08:00:00',14,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 21:00:00',14,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 06:00:00',16,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 03:00:00',16,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 03:00:00',16,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 13:00:00',16,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 08:00:00',16,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 15:00:00',23,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 15:00:00',23,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 23:00:00',23,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 16:00:00',23,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 13:00:00',23,17);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 01:00:00',1,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 17:00:00',1,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 23:00:00',1,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 14:00:00',1,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 21:00:00',1,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 18:00:00',4,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 07:00:00',4,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 12:00:00',4,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 20:00:00',4,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 17:00:00',4,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 08:00:00',7,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 09:00:00',7,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 05:00:00',7,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 22:00:00',7,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 11:00:00',8,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 17:00:00',8,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 03:00:00',8,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 13:00:00',8,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 21:00:00',8,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 11:00:00',14,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 03:00:00',14,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 14:00:00',14,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 14:00:00',14,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 22:00:00',14,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 05:00:00',19,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 16:00:00',19,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 02:00:00',19,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 13:00:00',19,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 23:00:00',19,18);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 07:00:00',1,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 15:00:00',1,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 07:00:00',1,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 21:00:00',1,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 21:00:00',1,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 06:00:00',7,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 17:00:00',7,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 14:00:00',7,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 22:00:00',7,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 06:00:00',10,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 09:00:00',10,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 20:00:00',10,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 20:00:00',10,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 20:00:00',10,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 02:00:00',11,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 20:00:00',11,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 13:00:00',11,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 06:00:00',11,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 20:00:00',11,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 05:00:00',15,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 19:00:00',15,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 03:00:00',15,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 09:00:00',15,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 23:00:00',15,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 18:00:00',24,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 23:00:00',24,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 02:00:00',24,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 08:00:00',24,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 23:00:00',24,19);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 09:00:00',3,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 21:00:00',3,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 00:00:00',3,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 04:00:00',3,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 15:00:00',3,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 18:00:00',6,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 23:00:00',6,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 01:00:00',6,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 11:00:00',6,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 19:00:00',6,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 14:00:00',12,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 21:00:00',12,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 00:00:00',12,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 15:00:00',12,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 21:00:00',12,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 04:00:00',14,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 09:00:00',14,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 14:00:00',14,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 02:00:00',14,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 23:00:00',14,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 01:00:00',16,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 07:00:00',16,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 23:00:00',16,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 20:00:00',16,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 22:00:00',16,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 04:00:00',17,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 23:00:00',17,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 00:00:00',17,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 21:00:00',17,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 23:00:00',17,20);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 03:00:00',2,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 20:00:00',2,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 00:00:00',2,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 15:00:00',2,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 18:00:00',2,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 05:00:00',5,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 19:00:00',5,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 05:00:00',5,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 06:00:00',5,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 23:00:00',5,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 10:00:00',6,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 21:00:00',6,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 07:00:00',6,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 23:00:00',6,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 03:00:00',7,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 15:00:00',7,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 20:00:00',7,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 07:00:00',7,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 22:00:00',7,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 04:00:00',8,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 17:00:00',8,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 21:00:00',8,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 20:00:00',8,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 18:00:00',8,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 03:00:00',21,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 01:00:00',21,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 19:00:00',21,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 14:00:00',21,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 23:00:00',21,21);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 10:00:00',1,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 20:00:00',1,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 09:00:00',1,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 19:00:00',1,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 22:00:00',1,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 02:00:00',9,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 22:00:00',9,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 21:00:00',9,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 09:00:00',9,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 22:00:00',9,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 08:00:00',14,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 14:00:00',14,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 22:00:00',14,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 08:00:00',14,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 23:00:00',14,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 04:00:00',18,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 01:00:00',18,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 09:00:00',18,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 18:00:00',18,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 21:00:00',18,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 06:00:00',20,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 15:00:00',20,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 00:00:00',20,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 23:00:00',20,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 07:00:00',21,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 11:00:00',21,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 11:00:00',21,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 12:00:00',21,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 10:00:00',21,22);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 13:00:00',3,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 14:00:00',3,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 23:00:00',3,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 17:00:00','2018-02-01 18:00:00',3,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 23:00:00',3,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 02:00:00',5,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 15:00:00',5,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 00:00:00',5,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 12:00:00',5,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 22:00:00',5,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 07:00:00',11,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 13:00:00',11,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 04:00:00',11,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 04:00:00','2018-02-01 15:00:00',11,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 15:00:00',11,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 13:00:00',12,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 20:00:00',12,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 03:00:00',12,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 15:00:00',12,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 22:00:00',12,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 08:00:00',16,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 11:00:00',16,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 03:00:00',16,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 06:00:00',16,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 19:00:00','2018-02-01 23:00:00',16,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 03:00:00',23,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 13:00:00',23,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 23:00:00',23,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 16:00:00','2018-02-01 17:00:00',23,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 21:00:00',23,23);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 07:00:00','2018-02-01 08:00:00',13,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 16:00:00',13,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 00:00:00','2018-02-01 09:00:00',13,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 09:00:00','2018-02-01 12:00:00',13,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 20:00:00','2018-02-01 21:00:00',13,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 03:00:00',15,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 23:00:00','2018-02-01 02:00:00',15,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 03:00:00',15,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 03:00:00',15,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 19:00:00',15,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 01:00:00','2018-02-01 02:00:00',18,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 17:00:00',18,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 03:00:00','2018-02-01 05:00:00',18,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 07:00:00',18,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 21:00:00',18,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 12:00:00','2018-02-01 13:00:00',21,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 14:00:00','2018-02-01 17:00:00',21,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 15:00:00','2018-02-01 20:00:00',21,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 11:00:00','2018-02-01 12:00:00',21,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 11:00:00',21,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 08:00:00',23,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 05:00:00','2018-02-01 10:00:00',23,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 22:00:00','2018-02-01 01:00:00',23,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 02:00:00','2018-02-01 03:00:00',23,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 10:00:00','2018-02-01 14:00:00',23,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 08:00:00','2018-02-01 11:00:00',24,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 18:00:00','2018-02-01 19:00:00',24,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 21:00:00','2018-02-01 23:00:00',24,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 06:00:00','2018-02-01 13:00:00',24,24);
INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES ('2018-01-01 13:00:00','2018-02-01 17:00:00',24,24);

INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (1,4,'2018-01-01 14:00:00','2018-01-01 15:00:00','No',54,1);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (1,24,'2018-01-01 14:00:00','2018-01-01 15:00:00','No',40,1);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (1,4,'2018-01-01 08:00:00','2018-01-01 09:00:00','No',84,1);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (1,13,'2018-01-01 09:00:00','2018-01-01 10:00:00','No',99,2);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (1,20,'2018-01-01 18:00:00','2018-01-01 19:00:00','No',89,2);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (1,7,'2018-01-01 13:00:00','2018-01-01 14:00:00','No',11,2);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (2,4,'2018-01-01 17:00:00','2018-01-01 18:00:00','No',77,3);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (2,4,'2018-01-01 06:00:00','2018-01-01 07:00:00','No',59,3);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (2,21,'2018-01-01 06:00:00','2018-01-01 07:00:00','No',41,3);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (2,16,'2018-01-01 05:00:00','2018-01-01 06:00:00','No',57,4);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (2,2,'2018-01-01 11:00:00','2018-01-01 12:00:00','No',89,4);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (2,16,'2018-01-01 03:00:00','2018-01-01 04:00:00','No',19,4);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (3,6,'2018-01-01 20:00:00','2018-01-01 21:00:00','No',29,5);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (3,19,'2018-01-01 21:00:00','2018-01-01 22:00:00','No',86,5);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (3,13,'2018-01-01 11:00:00','2018-01-01 12:00:00','No',88,5);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (3,9,'2018-01-01 09:00:00','2018-01-01 10:00:00','No',66,6);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (3,20,'2018-01-01 20:00:00','2018-01-01 21:00:00','No',7,6);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (3,20,'2018-01-01 18:00:00','2018-01-01 19:00:00','No',73,6);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (4,18,'2018-01-01 16:00:00','2018-01-01 17:00:00','No',5,7);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (4,14,'2018-01-01 04:00:00','2018-01-01 05:00:00','No',94,7);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (4,17,'2018-01-01 10:00:00','2018-01-01 11:00:00','No',60,7);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (4,24,'2018-01-01 20:00:00','2018-01-01 21:00:00','No',57,8);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (4,6,'2018-01-01 17:00:00','2018-01-01 18:00:00','No',70,8);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (4,6,'2018-01-01 08:00:00','2018-01-01 09:00:00','No',99,8);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (5,10,'2018-01-01 10:00:00','2018-01-01 11:00:00','No',90,9);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (5,14,'2018-01-01 21:00:00','2018-01-01 22:00:00','No',95,9);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (5,5,'2018-01-01 16:00:00','2018-01-01 17:00:00','No',9,9);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (5,11,'2018-01-01 07:00:00','2018-01-01 08:00:00','No',3,10);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (5,24,'2018-01-01 02:00:00','2018-01-01 03:00:00','No',67,10);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (5,6,'2018-01-01 18:00:00','2018-01-01 19:00:00','No',90,10);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (6,1,'2018-01-01 01:00:00','2018-01-01 02:00:00','No',28,11);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (6,1,'2018-01-01 09:00:00','2018-01-01 10:00:00','No',62,11);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (6,4,'2018-01-01 19:00:00','2018-01-01 20:00:00','No',64,11);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (6,2,'2018-01-01 13:00:00','2018-01-01 14:00:00','No',14,12);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (6,8,'2018-01-01 07:00:00','2018-01-01 08:00:00','No',86,12);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (6,8,'2018-01-01 02:00:00','2018-01-01 03:00:00','No',58,12);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (7,2,'2018-01-01 03:00:00','2018-01-01 04:00:00','No',13,13);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (7,16,'2018-01-01 01:00:00','2018-01-01 02:00:00','No',37,13);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (7,8,'2018-01-01 21:00:00','2018-01-01 22:00:00','No',6,13);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (7,9,'2018-01-01 00:00:00','2018-01-01 01:00:00','No',15,14);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (7,11,'2018-01-01 09:00:00','2018-01-01 10:00:00','No',31,14);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (7,15,'2018-01-01 17:00:00','2018-01-01 18:00:00','No',87,14);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (8,9,'2018-01-01 07:00:00','2018-01-01 08:00:00','No',73,15);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (8,9,'2018-01-01 02:00:00','2018-01-01 03:00:00','No',49,15);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (8,9,'2018-01-01 22:00:00','2018-01-01 23:00:00','No',21,15);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (8,10,'2018-01-01 12:00:00','2018-01-01 13:00:00','No',62,16);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (8,3,'2018-01-01 03:00:00','2018-01-01 04:00:00','No',28,16);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (8,12,'2018-01-01 19:00:00','2018-01-01 20:00:00','No',53,16);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (9,22,'2018-01-01 06:00:00','2018-01-01 07:00:00','No',95,17);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (9,24,'2018-01-01 18:00:00','2018-01-01 19:00:00','No',46,17);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (9,12,'2018-01-01 09:00:00','2018-01-01 10:00:00','No',50,17);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (9,3,'2018-01-01 10:00:00','2018-01-01 11:00:00','No',78,18);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (9,13,'2018-01-01 08:00:00','2018-01-01 09:00:00','No',1,18);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (9,18,'2018-01-01 05:00:00','2018-01-01 06:00:00','No',62,18);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (10,6,'2018-01-01 05:00:00','2018-01-01 06:00:00','No',39,19);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (10,14,'2018-01-01 19:00:00','2018-01-01 20:00:00','No',83,19);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (10,15,'2018-01-01 16:00:00','2018-01-01 17:00:00','No',94,19);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (10,18,'2018-01-01 06:00:00','2018-01-01 07:00:00','No',9,20);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (10,18,'2018-01-01 15:00:00','2018-01-01 16:00:00','No',65,20);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (10,9,'2018-01-01 23:00:00','2018-01-02 00:00:00','No',81,20);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (11,3,'2018-01-01 13:00:00','2018-01-01 14:00:00','No',38,21);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (11,8,'2018-01-01 03:00:00','2018-01-01 04:00:00','No',41,21);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (11,15,'2018-01-01 16:00:00','2018-01-01 17:00:00','No',94,21);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (11,2,'2018-01-01 19:00:00','2018-01-01 20:00:00','No',51,22);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (11,2,'2018-01-01 03:00:00','2018-01-01 04:00:00','No',19,22);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (11,2,'2018-01-01 06:00:00','2018-01-01 07:00:00','No',93,22);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (12,17,'2018-01-01 19:00:00','2018-01-01 20:00:00','No',8,23);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (12,17,'2018-01-01 13:00:00','2018-01-01 14:00:00','No',33,23);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (12,3,'2018-01-01 06:00:00','2018-01-01 07:00:00','No',22,23);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (12,18,'2018-01-01 06:00:00','2018-01-01 07:00:00','No',75,24);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (12,8,'2018-01-01 01:00:00','2018-01-01 02:00:00','No',12,24);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (12,15,'2018-01-01 11:00:00','2018-01-01 12:00:00','No',56,24);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (13,23,'2018-01-01 00:00:00','2018-01-01 01:00:00','No',95,25);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (13,16,'2018-01-01 03:00:00','2018-01-01 04:00:00','No',21,25);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (13,12,'2018-01-01 01:00:00','2018-01-01 02:00:00','No',90,25);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (13,23,'2018-01-01 08:00:00','2018-01-01 09:00:00','No',32,26);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (13,12,'2018-01-01 05:00:00','2018-01-01 06:00:00','No',96,26);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (13,12,'2018-01-01 04:00:00','2018-01-01 05:00:00','No',48,26);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (14,22,'2018-01-01 02:00:00','2018-01-01 03:00:00','No',34,27);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (14,16,'2018-01-01 16:00:00','2018-01-01 17:00:00','No',76,27);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (14,24,'2018-01-01 12:00:00','2018-01-01 13:00:00','No',70,27);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (14,1,'2018-01-01 17:00:00','2018-01-01 18:00:00','No',33,28);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (14,1,'2018-01-01 02:00:00','2018-01-01 03:00:00','No',30,28);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (14,1,'2018-01-01 10:00:00','2018-01-01 11:00:00','No',27,28);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (15,22,'2018-01-01 07:00:00','2018-01-01 08:00:00','No',94,29);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (15,6,'2018-01-01 19:00:00','2018-01-01 20:00:00','No',40,29);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (15,3,'2018-01-01 10:00:00','2018-01-01 11:00:00','No',12,29);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (15,5,'2018-01-01 20:00:00','2018-01-01 21:00:00','No',36,30);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (15,15,'2018-01-01 23:00:00','2018-01-02 00:00:00','No',54,30);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (15,5,'2018-01-01 12:00:00','2018-01-01 13:00:00','No',44,30);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (16,21,'2018-01-01 13:00:00','2018-01-01 14:00:00','No',4,31);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (16,5,'2018-01-01 08:00:00','2018-01-01 09:00:00','No',55,31);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (16,5,'2018-01-01 07:00:00','2018-01-01 08:00:00','No',23,31);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (16,21,'2018-01-01 18:00:00','2018-01-01 19:00:00','No',25,32);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (16,21,'2018-01-01 17:00:00','2018-01-01 18:00:00','No',56,32);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (16,18,'2018-01-01 16:00:00','2018-01-01 17:00:00','No',65,32);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (17,20,'2018-01-01 08:00:00','2018-01-01 09:00:00','No',5,33);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (17,20,'2018-01-01 06:00:00','2018-01-01 07:00:00','No',81,33);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (17,4,'2018-01-01 01:00:00','2018-01-01 02:00:00','No',7,33);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (17,1,'2018-01-01 17:00:00','2018-01-01 18:00:00','No',50,34);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (17,1,'2018-01-01 09:00:00','2018-01-01 10:00:00','No',60,34);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (17,4,'2018-01-01 01:00:00','2018-01-01 02:00:00','No',44,34);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (18,12,'2018-01-01 10:00:00','2018-01-01 11:00:00','No',66,35);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (18,17,'2018-01-01 11:00:00','2018-01-01 12:00:00','No',49,35);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (18,23,'2018-01-01 11:00:00','2018-01-01 12:00:00','No',77,35);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (18,22,'2018-01-01 21:00:00','2018-01-01 22:00:00','No',25,36);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (18,15,'2018-01-01 12:00:00','2018-01-01 13:00:00','No',65,36);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (18,8,'2018-01-01 09:00:00','2018-01-01 10:00:00','No',100,36);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (19,21,'2018-01-01 18:00:00','2018-01-01 19:00:00','No',10,37);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (19,4,'2018-01-01 23:00:00','2018-01-02 00:00:00','No',40,37);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (19,20,'2018-01-01 16:00:00','2018-01-01 17:00:00','No',55,37);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (19,9,'2018-01-01 09:00:00','2018-01-01 10:00:00','No',3,38);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (19,22,'2018-01-01 03:00:00','2018-01-01 04:00:00','No',26,38);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (19,12,'2018-01-01 09:00:00','2018-01-01 10:00:00','No',63,38);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (20,6,'2018-01-01 06:00:00','2018-01-01 07:00:00','No',59,39);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (20,22,'2018-01-01 20:00:00','2018-01-01 21:00:00','No',13,39);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (20,18,'2018-01-01 14:00:00','2018-01-01 15:00:00','No',30,39);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (20,22,'2018-01-01 05:00:00','2018-01-01 06:00:00','No',1,40);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id) VALUES (20,24,'2018-01-01 01:00:00','2018-01-01 02:00:00','No',7,40);
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (20,22,'2018-01-01 16:00:00','2018-01-01 17:00:00','No',36,40,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (21,20,'2018-01-01 17:00:00','2018-01-01 18:00:00','No',64,41,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (21,21,'2018-01-01 12:00:00','2018-01-01 13:00:00','No',62,41,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (21,21,'2018-01-01 06:00:00','2018-01-01 07:00:00','No',41,41,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (21,13,'2018-01-01 23:00:00','2018-01-02 00:00:00','No',88,42,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (21,19,'2018-01-01 21:00:00','2018-01-01 22:00:00','No',33,42,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (21,1,'2018-01-01 05:00:00','2018-01-01 06:00:00','No',63,42,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (22,11,'2018-01-01 07:00:00','2018-01-01 08:00:00','No',74,43,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (22,22,'2018-01-01 21:00:00','2018-01-01 22:00:00','No',58,43,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (22,5,'2018-01-01 09:00:00','2018-01-01 10:00:00','No',66,43,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (22,3,'2018-01-01 12:00:00','2018-01-01 13:00:00','No',73,44,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (22,14,'2018-01-01 12:00:00','2018-01-01 13:00:00','No',71,44,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (22,15,'2018-01-01 11:00:00','2018-01-01 12:00:00','No',21,44,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (23,22,'2018-01-01 09:00:00','2018-01-01 10:00:00','No',85,45,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (23,21,'2018-01-01 21:00:00','2018-01-01 22:00:00','No',26,45,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (23,16,'2018-01-01 19:00:00','2018-01-01 20:00:00','No',44,45,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (23,15,'2018-01-01 15:00:00','2018-01-01 16:00:00','No',3,46,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (23,19,'2018-01-01 17:00:00','2018-01-01 18:00:00','No',4,46,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (23,15,'2018-01-01 12:00:00','2018-01-01 13:00:00','No',91,46,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (24,14,'2018-01-01 08:00:00','2018-01-01 09:00:00','No',2,47,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (24,18,'2018-01-01 16:00:00','2018-01-01 17:00:00','No',15,47,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (24,18,'2018-01-01 10:00:00','2018-01-01 11:00:00','No',10,47,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (24,7,'2018-01-01 12:00:00','2018-01-01 13:00:00','No',38,48,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (24,11,'2018-01-01 00:00:00','2018-01-01 01:00:00','No',44,48,'successful');
INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES (24,13,'2018-01-01 04:00:00','2018-01-01 05:00:00','No',76,48,'successful');
