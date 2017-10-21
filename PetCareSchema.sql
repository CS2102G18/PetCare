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

CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE pets_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE request_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE avail_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE assn_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;

CREATE TABLE petcategory(
    pcat_id INT PRIMARY KEY,
    size VARCHAR(20),
    age VARCHAR(10),
    name VARCHAR(100)
);

CREATE TABLE pet_user(
    user_id INT PRIMARY KEY DEFAULT nextval('user_id_seq'),
    name VARCHAR(64) NOT NULL,
    password VARCHAR(64) NOT NULL,
    email VARCHAR(64) UNIQUE,
    address VARCHAR(64)
);

CREATE TABLE pet(
    pets_id INT PRIMARY KEY DEFAULT nextval('pets_id_seq'),
    owner_id INT REFERENCES pet_user(user_id),
    pcat_id INT REFERENCES petcategory(pcat_id)
);

CREATE TABLE availability(
    avail_id INT PRIMARY KEY DEFAULT nextval('avail_id_seq'),
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    pcat_id INT REFERENCES petcategory(pcat_id),
    taker_id INT REFERENCES pet_user(user_id),
    is_deleted BOOLEAN DEFAULT FALSE
);

CREATE TABLE request(
    request_id INT PRIMARY KEY DEFAULT nextval('request_id_seq'),
    owner_id INT REFERENCES pet_user(user_id),
    taker_id INT REFERENCES pet_user(user_id),
    care_begin TIMESTAMP NOT NULL,
    care_end TIMESTAMP NOT NULL,
    remarks VARCHAR(64),
    bids NUMERIC NOT NULL,
    pets_id INT REFERENCES pet(pets_id),
    status VARCHAR(20) CHECK (status IN ('pending', 'failed', 'successful', 'cancelled')) DEFAULT 'pending'
);

CREATE TABLE assignment(
    assm_id INT PRIMARY KEY DEFAULT nextval('assn_id_seq'),
    request_id INT REFERENCES request(request_id),
    price NUMERIC,
    is_done BOOLEAN DEFAULT FALSE,
    is_paid BOOLEAN DEFAULT FALSE
);