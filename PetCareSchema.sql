DROP TABLE assignment;
DROP TABLE request
DROP TABLE availability;
DROP TABLE pet;
DROP TABLE petcategory;
DROP TABLE pet_user;
DROP TABLE util_age;
DROP TABLE util_size;
DROP TABLE util_species;

DROP SEQUENCE user_id_seq;
DROP SEQUENCE pets_id_seq;
DROP SEQUENCE request_id_seq;
DROP SEQUENCE avail_id_seq;
DROP SEQUENCE assn_id_seq;
DROP SEQUENCE pcat_seq;

﻿CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE pets_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE request_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE avail_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE assn_id_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;
CREATE SEQUENCE pcat_seq INCREMENT BY 1 MINVALUE 0 START WITH 1 NO CYCLE;

CREATE TABLE util_age(
    age VARCHAR(10) PRIMARY KEY CONSTRAINT CHK_ag CHECK (age in ('puppy', 'adult'))
);

CREATE TABLE util_size(
    size VARCHAR(20) PRIMARY KEY CONSTRAINT CHK_sz CHECK (size in ('small', 'medium', 'large', 'giant'))
);

CREATE TABLE util_species(
    species VARCHAR(30) PRIMARY KEY CONSTRAINT CHK_sp CHECK (species in ('cat', 'dog', 'rabbit', 'lizard', 'others'))
);

CREATE TABLE petcategory(
    pcat_id INT PRIMARY KEY DEFAULT nextval('pcat_seq'),
    age VARCHAR(10) REFERENCES util_age(age),
    size VARCHAR(20) REFERENCES util_size(size),
    species VARCHAR(30) REFERENCES util_species(species)
);

INSERT INTO util_age(age) VALUES('puppy');
INSERT INTO util_age(age) VALUES('adult');

INSERT INTO util_size(size) VALUES('small');
INSERT INTO util_size(size) VALUES('medium');
INSERT INTO util_size(size) VALUES('large');
INSERT INTO util_size(size) VALUES('giant');

INSERT INTO util_species(species) VALUES('dog');
INSERT INTO util_species(species) VALUES('rabbit');
INSERT INTO util_species(species) VALUES('cat');
INSERT INTO util_species(species) VALUES('lizard');
INSERT INTO util_species(species) VALUES('others');

INSERT INTO petcategory(age, size, species)
    SELECT *
    FROM util_age, util_size, util_species;

CREATE TABLE pet_user(
    user_id INT PRIMARY KEY DEFAULT nextval('user_id_seq'),
    name VARCHAR(64) NOT NULL,
    password VARCHAR(64) NOT NULL,
    email VARCHAR(64) UNIQUE,
    address VARCHAR(64),
    role VARCHAR(10) DEFAULT 'normal' CONSTRAINT CHK_role CHECK (role in ('admin', 'normal'))
);

CREATE TABLE pet(
    pets_id INT PRIMARY KEY DEFAULT nextval('pets_id_seq'),
    owner_id INT REFERENCES pet_user(user_id) ON DELETE CASCADE,
    pcat_id INT REFERENCES petcategory(pcat_id) ON DELETE CASCADE ON UPDATE CASCADE,
    pet_name VARCHAR(64),
    UNIQUE (owner_id, pet_name)
);

CREATE TABLE availability(
    avail_id INT PRIMARY KEY DEFAULT nextval('avail_id_seq'),
    post_time timestamp NOT NULL DEFAULT current_timestamp,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    pcat_id INT REFERENCES petcategory(pcat_id) ON DELETE CASCADE ON UPDATE CASCADE,
    taker_id INT REFERENCES pet_user(user_id) ON DELETE CASCADE,
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
    remarks VARCHAR(64),
    bids NUMERIC NOT NULL,
    pets_id INT REFERENCES pet(pets_id) ON DELETE CASCADE ON UPDATE CASCADE,
    slot VARCHAR(64),
    status VARCHAR(20) CHECK (status IN ('pending', 'failed', 'successful', 'cancelled')) DEFAULT 'pending',
    CONSTRAINT CHK_start_end CHECK (care_end > care_begin),
    CONSTRAINT CHK_post CHECK (care_begin > post_time)
);

CREATE TABLE assignment(
    assm_id INT PRIMARY KEY DEFAULT nextval('assn_id_seq'),
    request_id INT REFERENCES request(request_id) ON DELETE CASCADE ON UPDATE CASCADE,
    price NUMERIC,
    is_done BOOLEAN DEFAULT FALSE,
    is_paid BOOLEAN DEFAULT FALSE
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

CREATE OR REPLACE FUNCTION addRequestSlot()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE request
    SET slot = timeslot(new.request_id)
    WHERE request_id = new.request_id;
    RETURN NULL;
END; $$
LANGUAGE PLPGSQL;

CREATE TRIGGER addSlot
AFTER INSERT
ON request
FOR EACH ROW
EXECUTE PROCEDURE addRequestSlot();

INSERT INTO pet_user(name, password, email, address, role) VALUES ('Xia Rui',12345,'e0012672@u.nus.edu','30 Ang Mo Kio Ave 8', 'admin');
INSERT INTO pet_user(name, password, email, address, role) VALUES ('Chen Penghao',12345,'e0004801@u.nus.edu','33 Lorong 2 Toa Payoh', 'admin');
INSERT INTO pet_user(name, password, email, address, role) VALUES ('Xie Peiyi',12345,'peiyi@u.nus.edu','55 Hougang Ave 10', 'admin');
INSERT INTO pet_user(name, password, email, address, role) VALUES ('Kuang Ming',12345,'km@msn.com','', 'admin');

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
