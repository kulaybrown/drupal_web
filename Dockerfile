FROM drupal:11-apache

# Add Drush so we can install the site, enable the theme/module, and set
# config from the command line instead of only through the web installer.
# Drush 13 is the version compatible with Drupal 11.
RUN composer require drush/drush:^13 --no-interaction

ENV PATH="/opt/drupal/vendor/bin:${PATH}"

# Fix sites/default ownership/permissions on every start (see script for why),
# then continue with the base image's normal entrypoint and CMD.
COPY fix-permissions-entrypoint.sh /usr/local/bin/fix-permissions-entrypoint.sh
RUN chmod +x /usr/local/bin/fix-permissions-entrypoint.sh
ENTRYPOINT ["/usr/local/bin/fix-permissions-entrypoint.sh"]
CMD ["apache2-foreground"]
