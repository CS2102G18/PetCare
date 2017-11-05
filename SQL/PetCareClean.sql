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

DROP FUNCTION timeslot;
DROP FUNCTION calculateTotalTime;
DROP FUNCTION addRequestInfo;
DROP FUNCTION cleanOutdatedAvail;
DROP FUNCTION cleanOutdatedReq;
DROP TRIGGER addSlot;
DROP TRIGGER changeAvail;
DROP TRIGGER changeReq;