--BUDGET--'2019-12-23'
SELECT  handyman_id
FROM    handyman_services hs
WHERE   start_price <= $BUDGET 
--AND     end_price >= $BUDGET

--SERVICE
SELECT  *
FROM    handyman_services
WHERE   service_id = $SERVICEID

--DATE--
SELECT  handyman_id 
FROM    handyman_working_days_time
WHERE   DAYNAME($DATE) = SUBSTR(day_name, 1, CHAR_LENGTH(day_name)-1)

--TIME--
SELECT  handyman_id 
FROM    handyman_working_days_time
WHERE   start_time <= $TIME 
AND     end_time >= $TIME

--ADDRESS-- (problem: a handyman with two  addresses in range, solution: take nearest address)
SELECT  ur.*, u.type
FROM    user_addresses ur, users u
WHERE   u.type = 'handyman'
AND     ur.uid = u.uid
AND     haversine(-20.4503000, 57.5575000, lat, lng) < 8

    --solution
SELECT      ur.uid, MIN(haversine(-20.4503000, 57.5575000, ur.lat, ur.lng)) as distance
FROM        user_addresses ur, users u
WHERE       u.type = 'handyman'
AND         ur.uid = u.uid
AND         haversine(-20.4503000, 57.5575000, ur.lat, ur.lng) < 8      
GROUP BY    ur.uid

---------------------------------------------------------------------------------
--MERGE ALL ATRRIBUTES
SELECT      hs.handyman_id, u.username, u.bio, hs.start_price, hs.end_price, 
                    ROUND(MIN(HAVERSINE(-20.4503000, 57.5575000, ua.lat, ua.lng)),2) as distance,
                    IFNULL( ROUND(AVG(ur.rating),1), 0) as rating, u.picture
FROM        handyman_services hs LEFT JOIN user_ratings ur ON hs.handyman_id = ur.uid,
                users u, handyman_working_days_time hwdt, user_addresses ua
--service
WHERE       hs.service_id = 9
AND         hs.handyman_id = u.uid
--date
AND         hs.handyman_id = hwdt.handyman_id
AND         DAYNAME('2019-12-23') = SUBSTR(day_name, 1, CHAR_LENGTH(day_name)-1)
--time
AND         start_time <= '10:00'
AND         end_time >= '10:00'
--budget
AND         hs.start_price <= 1000
--address
AND         hs.handyman_id = ua.uid
AND         HAVERSINE(-20.4503000, 57.5575000, ua.lat, ua.lng) < 8 
GROUP BY    hs.handyman_id


---building query
SELECT hs.handyman_id, u.username, u.bio, hs.start_price, hs.end_price, 
       ROUND(MIN(HAVERSINE(-20.4503000, 57.5575000, ua.lat, ua.lng)),2) as 'distance(km)',
            IFNULL( ROUND(AVG(ur.rating),1), 0) as rating
FROM  handyman_services hs LEFT JOIN user_ratings ur ON hs.handyman_id = ur.uid,
        users u, handyman_working_days_time hwdt, user_addresses ua
WHERE hs.service_id = 9 
AND hs.handyman_id = u.uid 
AND hs.handyman_id = hwdt.handyman_id 
AND DAYNAME('2019-12-23') = SUBSTR(day_name, 1, CHAR_LENGTH(day_name)-1)
AND start_time <= '10:00'
AND end_time >= '10:00'
AND hs.start_price <= 800 
AND hs.handyman_id = ua.uid
AND HAVERSINE(-20.4503000, 57.5575000, ua.lat, ua.lng) < 11
GROUP BY    hs.handyman_id



--All handyman with service_id
SELECT us.handyman_id, us.username, us.bio, us.start_price, us.end_price, IFNULL( ROUND(AVG(ur.rating),1), 0) as rating [, distance]
FROM    (
    SELECT hs.handyman_id, u.username, u.bio, hs.start_price, hs.end_price 
    FROM users u,handyman_services hs 
    WHERE hs.service_id = 1 
    AND hs.handyman_id = u.uid
) us LEFT JOIN user_ratings ur ON us.handyman_id = ur.uid






--------------------
T($lat, $lng) =  (6371 * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lon)) + sin(radians($lat)) * sin(radians(latitude))))
--returns distance

--test
SELECT ur.*, u.type 
FROM user_addresses ur, users u 
WHERE u.type = 'handyman' 
AND ur.uid = u.uid 
AND haversine(-20.4503000, 57.5575000, lat, lng) < 5

SELECT ur.*, u.type 
FROM user_addresses ur, users u 
WHERE u.type = 'handyman' 
AND ur.uid = u.uid 
AND (6371 * acos(cos(radians(-20.4503000)) * cos(radians(lat)) * cos(radians(lng) - radians(57.5575000)) + sin(radians(-20.4503000)) * sin(radians(lat)))) < 5

SELECT * 
FROM user_addresses 
where (6371 * acos(cos(radians(-20.4503000)) * cos(radians(lat)) * cos(radians(lng) - radians(57.5575000)) + sin(radians(-20.4503000)) * sin(radians(lat)))) < 5

SELECT *
FROM user_addresses 
WHERE (6371 * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lon)) + sin(radians($lat)) * sin(radians(latitude)))) < 5;
--HAVING distance < 25 
--ORDER BY distance 


BEGIN
    RETURN (6371 * acos(cos(radians(lat1)) * 
                    cos(radians(lat2)) * 
                    cos(radians(lng2) - radians(lng1)) + 
                    sin(radians(lat1)) * sin(radians(lat2))));
END

SELECT  *, haversine(-20.4503000, 57.5575000, lat, lng) as 'distance from britannia'
FROM    user_addresses


--============================================================================================================
--============================================================================================================
--============================================================================================================

jobDetails = { date, budget, {lat, lng} , time }

SELECT  hs.handyman_id, hs.service_id, start_price, end_price,
        hwdt.day_name, hwdt.start_time, hwdt.end_time,
        ua.address

FROM    handyman_services hs,
        handyman_working_days_time hwdt,
        user_addresses ua

WHERE   hs.service_id = 14
AND     hs.handyman_id = hwdt.handyman_id
AND     hs.handyman_id = hwdt.handyman_id
AND     hs.handyman_id = ua.uid
AND hs.handyman_id = 'nwBfWEivmoTyZGfAywX6cEAg34g1'


MATCH_SCORE(
    handymanDayName, handymanStartPrice, 
    handymanLat, handymanLng, handymanStartTime,
    jobDate, jobBudget, jobLat, jobLng, jobTime
    )


SELECT      hs.handyman_id, MAX( MATCH_SCORE(...) ) as score

FROM        handyman_services hs,
            handyman_working_days_time hwdt,
            user_addresses ua

WHERE       hs.service_id = ?
AND         hs.handyman_id = hwdt.handyman_id
AND         hs.handyman_id = hwdt.handyman_id
AND         hs.handyman_id = ua.uid
GROUP BY    hs.handyman_id


-- get handyman with their attributes to be used for matching
SELECT  hs.handyman_id, hwdt.day_name, hwdt.start_time, hwdt.end_time, ua.lat, ua.lng, hs.start_price, hs.end_price
FROM    handyman_services hs,handyman_working_days_time hwdt, user_addresses ua
WHERE   hs.service_id = :service_id
AND     hs.handyman_id = hwdt.handyman_id
AND     hs.handyman_id = ua.uid

--get job attribute with its attribute to be used for matching
SELECT  j.service_id, j.date, j.budget, j.address_lat, j.address_lng, j.time
FROM    jobs j
WHERE   j.job_id = :job_id


                    IFNULL( ROUND(AVG(ur.rating),1), 0) as rating, u.picture
FROM        handyman_services hs LEFT JOIN user_ratings ur ON hs.handyman_id = ur.uid,

--matching query
SELECT  hs.handyman_id, u.username, u.bio, u.picture, hs.start_price, hs.end_price,
        ROUND(HAVERSINE(ua.lat, ua.lng, j.address_lat, j.address_lng),1) as distance,
        IFNULL( ROUND(AVG(ur.rating),1), 0) as rating,
                    MAX(MATCH_SCORE(
                        hwdt.day_name, hwdt.start_time, hwdt.end_time, ua.lat, ua.lng, hs.start_price, hs.end_price,
                        j.date, j.budget, j.address_lat, j.address_lng, j.time
                    )) as score
FROM    handyman_working_days_time hwdt, user_addresses ua, jobs j, users u,
        handyman_services hs LEFT JOIN user_ratings ur ON hs.handyman_id = ur.uid
WHERE   j.job_id = :job_id
AND     hs.service_id = j.service_id
AND     hs.handyman_id = hwdt.handyman_id
AND     hs.handyman_id = ua.uid
AND     hs.handyman_id = u.uid
GROUP BY hs.handyman_id
ORDER BY score DESC

CREATE FUNCTION MATCH_SCORE(
    hday_name, hstart_time, hend_time, haddress_lat, haddress_lng, hstart_price, hend_price,
    jdate, jbudget, jaddress_lat, jaddress_lng, jtime
) 
RETURNS DECIMAL(10,9)
DETERMINISTIC
BEGIN

    DECLARE d DECIMAL(10,9);
    DECLARE b DECIMAL(10,9);
    DECLARE a DECIMAL(10,9);
    DECLARE t DECIMAL(10,9);
    DECLARE score DECIMAL(10,9);

    IF CONCAT(DAYNAME(jdate),'s') = hday_name THEN
        SET d = 1;
    ELSE
        SET d = 0;
    END IF;

    SET b = (jbudget - hstart_price) / jbudget;

    SET a = 1 / HAVERSINE(jaddress_lat, jaddress_lng, haddress_lat, haddress_lng);

    SET t = 1 / (jtime - hstart_time);

    SET score = ( d*(t+4) + (b*3) + (a*2) );

    RETURN score;

END$$
DELIMITER ;



SELECT hs.handyman_id,hwdt.day_name, hwdt.start_time, hwdt.end_time, ua.lat, ua.lng, hs.start_price, hs.end_price, j.date, j.budget, j.address_lat, j.address_lng, j.time, MATCH_SCORE( hwdt.day_name, hwdt.start_time, hwdt.end_time, ua.lat, ua.lng, hs.start_price, hs.end_price, j.date, j.budget, j.address_lat, j.address_lng, j.time ) as score FROM handyman_services hs,handyman_working_days_time hwdt, user_addresses ua, jobs j WHERE j.job_id = 25 AND hs.service_id = j.service_id AND hs.handyman_id = hwdt.handyman_id AND hs.handyman_id = ua.uid


SELECT 1/HAVERSINE(-20.4002800, 57.5966700, -20.4502800, 57.5575000) as score,
 HAVERSINE(-20.4002800, 57.5966700, -20.4502800, 57.5575000) ,
  1/HAVERSINE(-20.4914596, 57.4633231, -20.4502800, 57.5575000) as score,
   HAVERSINE(-20.4914596, 57.4633231, -20.4502800, 57.5575000)

BEGIN
	DECLARE a DECIMAL(10,9);
    SET a = 1 / HAVERSINE(-20.4002800, 57.5966700, -20.4502800, 57.5575000)
    RETURN a;
END




--================================================================================================
--================================================================================================
--================================================================================================
--================================================================================================

--address score
select address, lat, lng, 
        HAVERSINE(-20.4904850, 57.5585465, lat, lng) as distance, 
        DISTANCE_SCORE(-20.4904850, 57.5585465, lat, lng) 
from user_addresses

--budget score
select 	500 as budget, hs.start_price, hs.end_price, BUDGET_SCORE(500, hs.start_price, hs.end_price) as score
FROM	handyman_services hs
order by score desc

--time score
SELECT  j.time, h.start_time, h.end_time, TIME_SCORE(j.time, h.start_time, h.end_time) as score
FROM 	`handyman_working_days_time` h, jobs j
WHERE 	j.job_id=1
order by score desc

--date score
SELECT  j.date, DAYNAME(j.date), h.day_name, DATE_SCORE(j.date, h.day_name) as score
FROM 	`handyman_working_days_time` h, jobs j
WHERE 	j.job_id=2
order by score desc



--old score function
BEGIN

    DECLARE d DECIMAL(10,9);
    DECLARE b DECIMAL(10,9);
    DECLARE a DECIMAL(10,9);
    DECLARE t DECIMAL(10,9);
    DECLARE score DECIMAL(10,9);

    IF CONCAT(DAYNAME(jdate),'s') = hday_name THEN
        SET d = 1;
    ELSE
        SET d = 0;
    END IF;

    SET b = (jbudget - hstart_price) / jbudget;

	SET a = 1 / HAVERSINE(jaddress_lat, jaddress_lng, haddress_lat, haddress_lng);

    SET t = 1 / (jtime - hstart_time);

    SET score = ( d*(t+4) + (b*3) + (a*2));

    RETURN score;

END

--new score function

BEGIN

    DECLARE d DECIMAL(10,9);
    DECLARE b DECIMAL(10,9);
    DECLARE a DECIMAL(10,9);
    DECLARE t DECIMAL(10,9);
    DECLARE score DECIMAL(10,9);

    SET d = DATE_SCORE(jdate, hday_name);

    SET b = BUDGET_SCORE(jbudget, hstart_price, hend_price);

	SET a = DISTANCE_SCORE(jaddress_lat, jaddress_lng, haddress_lat, haddress_lng);

    SET t = TIME_SCORE(jtime, hstart_time, hend_time);

    SET score = ( d*(t+4) + (b*3) + (a*2));

    RETURN score;

END

--test match function
SELECT hs.handyman_id,hwdt.day_name, hwdt.start_time, hwdt.end_time,hs.start_price, hs.end_price, j.date, DAYNAME(j.date) as job_day, 					j.budget, j.time,
		HAVERSINE(j.address_lat, j.address_lng,  ua.lat, ua.lng) as distance,
		BUDGET_SCORE(j.budget, hs.start_price, hs.end_price) as budget_score,
        DISTANCE_SCORE(j.address_lat, j.address_lng, ua.lat, ua.lng) as distance_score,
        TIME_SCORE(j.time, hwdt.start_time, hwdt.end_time) as time_score,
        DATE_SCORE(j.date, hwdt.day_name) as date_score,
		MATCH_SCORE( hwdt.day_name, hwdt.start_time,hwdt.end_time, ua.lat, ua.lng, hs.start_price, hs.end_price, j.date, j.budget, j.address_lat, 			j.address_lng, j.time ) as score
FROM handyman_services hs,handyman_working_days_time hwdt, user_addresses ua, jobs j 
WHERE j.job_id = 2
AND hs.service_id = j.service_id 
AND hs.handyman_id = hwdt.handyman_id 
AND hs.handyman_id = ua.uid


------------------------------------------------
-- EXCLUDE unavailable handymen

SELECT  js.handyman_id
FROM    jobs j, job_status js
WHERE   j.date = :DATE
AND     (SUBTIME(j.time, '03:00') < :TIME AND ADDTIME(j.time, '03:00') >= :TIME)
AND     j.job_id = js.job_id
AND     js.status = 'ongoing'


-- query to cope with handyman with no availability
SELECT  hs.handyman_id, u.username, u.bio, u.picture, hs.start_price, hs.end_price,
        ROUND(HAVERSINE(ua.lat, ua.lng, j.address_lat, j.address_lng),1) as distance,
        IFNULL( ROUND(AVG(ur.rating),1), 0) as rating,
                    MAX(MATCH_SCORE(
                        hwdt.day_name, hwdt.start_time, hwdt.end_time, ua.lat, ua.lng, hs.start_price, hs.end_price,
                        j.date, j.budget, j.address_lat, j.address_lng, j.time
                    )) as score
FROM    user_addresses ua, jobs j, users u,
        (handyman_services hs LEFT JOIN user_ratings ur ON hs.handyman_id = ur.uid)
        LEFT JOIN handyman_working_days_time hwdt ON hs.handyman_id = hwdt.handyman_id
WHERE   j.job_id = 1
AND     hs.service_id = j.service_id
AND     hs.handyman_id = ua.uid
AND     hs.handyman_id = u.uid
GROUP BY hs.handyman_id
ORDER BY score DESC