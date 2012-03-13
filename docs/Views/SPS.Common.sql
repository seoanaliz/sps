CREATE OR REPLACE VIEW "getSiteParams" AS
SELECT "public"."siteParams"."siteParamId"
	, "public"."siteParams"."alias"
	, "public"."siteParams"."value"
	, "public"."siteParams"."description"
	, "public"."siteParams"."statusId"
 FROM "public"."siteParams"
	WHERE "public"."siteParams"."statusId" IN (1,2);

CREATE OR REPLACE VIEW "getMetaDetails" AS
SELECT "public"."metaDetails"."metaDetailId"
	, "public"."metaDetails"."url"
	, "public"."metaDetails"."pageTitle"
	, "public"."metaDetails"."metaKeywords"
	, "public"."metaDetails"."metaDescription"
	, "public"."metaDetails"."alt"
	, "public"."metaDetails"."isInheritable"
	, "public"."metaDetails"."statusId"
 FROM "public"."metaDetails"
	WHERE "public"."metaDetails"."statusId" IN (1,2)
ORDER BY "url";

CREATE OR REPLACE VIEW "getStaticPages" AS
SELECT "public"."staticPages"."staticPageId"
	, "public"."staticPages"."title"
	, "public"."staticPages"."url"
	, "public"."staticPages"."content"
	, "public"."staticPages"."pageTitle"
	, "public"."staticPages"."metaKeywords"
	, "public"."staticPages"."metaDescription"
	, "public"."staticPages"."orderNumber"
	, "public"."staticPages"."parentStaticPageId"
	, "public"."staticPages"."statusId"
	, "parentStaticPage"."staticPageId" AS "parentStaticPage.staticPageId"
	, "parentStaticPage"."title" AS "parentStaticPage.title"
	, "parentStaticPage"."url" AS "parentStaticPage.url"		
	, "parentStaticPage"."parentStaticPageId" AS "parentStaticPage.parentStaticPageId"
 FROM "public"."staticPages"
	LEFT JOIN "public"."staticPages" "parentStaticPage" ON
		"parentStaticPage"."staticPageId" = "public"."staticPages"."parentStaticPageId"
	WHERE "public"."staticPages"."statusId" IN (1,2)
ORDER BY "orderNumber", "url";

CREATE OR REPLACE VIEW "getNavigationTypes" AS
SELECT "public"."navigationTypes"."navigationTypeId"
	, "public"."navigationTypes"."title"
	, "public"."navigationTypes"."alias"
	, "public"."navigationTypes"."statusId"
 FROM "public"."navigationTypes"
	WHERE "public"."navigationTypes"."statusId" IN (1,2)
ORDER BY "alias";

CREATE OR REPLACE VIEW "getNavigations" AS
SELECT "public"."navigations"."navigationId"
	, "public"."navigations"."navigationTypeId"
	, "public"."navigations"."title"
	, "public"."navigations"."orderNumber"
	, "public"."navigations"."staticPageId"
	, "public"."navigations"."url"
	, "public"."navigations"."statusId"
	, "navigationType"."navigationTypeId" AS "navigationType.navigationTypeId"
	, "navigationType"."title" AS "navigationType.title"
	, "navigationType"."alias" AS "navigationType.alias"
	, "staticPage"."staticPageId" AS "staticPage.staticPageId"
	, "staticPage"."title" AS "staticPage.title"
	, "staticPage"."url" AS "staticPage.url"
	, "staticPage"."parentStaticPageId" AS "staticPage.parentStaticPageId"
 FROM "public"."navigations"
	INNER JOIN "public"."navigationTypes" "navigationType" ON
		"navigationType"."navigationTypeId" = "public"."navigations"."navigationTypeId"
	LEFT JOIN "public"."staticPages" "staticPage" ON
		"staticPage"."staticPageId" = "public"."navigations"."staticPageId"
	WHERE "public"."navigations"."statusId" IN (1,2)
ORDER BY "navigationType"."alias", "orderNumber";