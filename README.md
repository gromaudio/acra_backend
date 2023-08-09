Crash Report Dashboard
======================

The Crash Report Dashboard is a Backend for `Application Crash Report for Android` AKA `ACRA`.


How to install the Dashboard
----------------------------

See the Installation Guide in the official Wiki. 


Implement ACRA in your app
--------------------------

The basic implementation of `ACRA` in your app is easily done. 
See the Basic Installation Wiki Page for information.

Grouping
--------------------------
As long as we don't group issues when they're submited because it's time consuming, we need to group them occasionally.<br/>
Example:<br/>
/group.php?appid=f5ar7wfpkdmda852krjpwmt8iunu4d9f&offset=0&count=100&lengthDiffPercent=85&group=1&length=15000<br/>

**offset** – offset from the start

**count** – max number of crashes to select

**lengthDiffPercent** (default 85) – use _similar_text_ for crashes that have stacktrace length differance not more than (100 - lengthDiffPercent)<br/><br/>
For example, if there're stacktraces:<br/>
A = 100 chars,<br/>
B = 80 chars,<br/>
C = 90 chars.<br/>
lengthDiffPercent = 85%.<br/>

B / A = 80%, stacktraces _will not_ be compared.<br/>
C / A = 90%, stacktraces _will_ be compared,<br/>
B / C = 88%, stacktraces _will_ be compared.<br/><br/>

If there're lots of issues to group, set this parameter to 99 first and gradually decrease > 98 > 95 > 90 > 85 with every request.<br/>
Most of stacktraces that belong to the same issue are identical, so it would group issues much faster.<br/>

**group** (default disabled) – group by number of occurances, desc, so the issues would found possible matches quicker.

**length** (default 15000) – limit stacktrace length

TODO:
* remember the last issue id (when group=false) for each app during the grouping and group only new issues further on.

SQL
--------------------------

Requests to clean up the database.

1. Delete all issues that have only 1 occurance.<br/>

DELETE FROM crashes WHERE id IN (
		SELECT * FROM ( SELECT `id` from `crashes` WHERE `appid` = 'f5ar7wfpkdmda852krjpwmt8iunu4d9f' and (status = 0 or status = 1) and CHAR_LENGTH(stack_trace) < 15000 group by issue_id HAVING count(id)=1 ) AS p
)


2. Delete old issues.<br/>

// ANR & tombstones<br/>
DELETE FROM `crashes` WHERE (appid="n7yjvztxh97d76jy4ek5ax4uc3d9cgx7" or appid="f5ar7wfpkdmda852krjpwmt8iunu4d9f") and application_log NOT LIKE "%V2OVL3.50.%"

// Dashlinq, VLineService, KnobHelper, MyCar<br/>
DELETE FROM crashes WHERE (appid="95wjw673hkkiw37rcumqarrwiczcqpk3" or appid="72gym8mf5juqjwxk43y8m47ygq3nnab8" or appid="88wjw673hkkiw37rcumqarrwiczcqpk3" or appid="5ztxh97ax4uc3n7yjvd76jy4ekd9cgx7") and custom_data NOT LIKE "%V2OVL3.69.%"


