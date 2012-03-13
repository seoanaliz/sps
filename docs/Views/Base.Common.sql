CREATE OR REPLACE VIEW "getStatuses" AS
SELECT "public"."statuses"."statusId"
	, "public"."statuses"."title"
	, "public"."statuses"."alias"
 FROM "public"."statuses";
	
CREATE OR REPLACE VIEW "getDaemonLocks" AS
SELECT "public"."daemonLocks"."daemonLockId"
	, "public"."daemonLocks"."title"
	, "public"."daemonLocks"."packageName"
	, "public"."daemonLocks"."methodName"
	, "public"."daemonLocks"."runAt"
	, "public"."daemonLocks"."maxExecutionTime"
	, ( now() - "runAt" < "maxExecutionTime" ) as "isActive"
 FROM "public"."daemonLocks";
 
CREATE OR REPLACE VIEW "getUsers" AS
SELECT "public"."users"."userId"
	, "public"."users"."login"
	, "public"."users"."password"
	, "public"."users"."statusId"
 FROM "public"."users"
	WHERE "public"."users"."statusId" IN (1,2);