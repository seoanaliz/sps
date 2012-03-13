#
# $Header: /data/cvs/1adw/eaze/Makefile,v 1.7 2008/03/17 08:05:10 sergeyfast Exp $
# $Revision: 1.7 $
# $Date: 2008/03/17 08:05:10 $
#

# Default Parameters
layout=local

include build.properties

.PHONY: sync deploy
verify:
	@# Verify layout
	@if [ "x" = "x$(deploy.$(layout).host)" ] && [ "xlocal" != "x$(layout)" ]; then \
		echo "Can not find layout \"$(layout)\"."; \
		exit 1; \
	fi;
	
sync:
	@echo "[SVN] Synchronizing with SVN server..."
	@svn update
	
sync-only:
	@echo "[SVN] Synchronizing with SVN server... w/o"
	@svn update --ignore-externals

deploy:
	@echo "[SSH] Deploying to server..."

	@# Synchronize
	@if [ "xlocal" != "x$(layout)" ]; then \
		rsync -Cavuz -e "ssh " $(deploy.$(layout).ignore) $(dir.deploy)/ "$(deploy.$(layout).user)"@$(deploy.$(layout).host):"$(deploy.$(layout).root)"; \
		ssh $(deploy.$(layout).user)@$(deploy.$(layout).host) "chmod +x $(deploy.$(layout).root)/post_deploy.sh && $(deploy.$(layout).root)/post_deploy.sh $(deploy.$(layout).root)";  \
	fi;