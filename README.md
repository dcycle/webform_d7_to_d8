Webform Drupal 7 to Drupal 8
-----

A Drupal 8 module to migrate webforms and their submissions from Drupal 7 to Drupal 8.

For more information on why this was create, see the blog post [Migrating Webforms from Drupal 7 to Drupal 8, Dec. 18, 2017, Dcycle Blog](http://blog.dcycle.com/blog/2017-12-18/migrating-webforms-drupal7-to-drupal8)

Installing
-----

You should have access to both your Drupal 8 site and your Drupal 7 database.

Install and enable `webform_d7_to_d8` as you would any other Drupal 8 module.

In the `$databases` section of your Drupal 8 `settings.php` file, add something like:

    $databases['upgrade']['default'] = array (
      'database' => 'drupal7database',
      'username' => 'drupal7user',
      'password' => 'drupal7password',
      'prefix' => '',
      'host' => 'drupal7host',
      'port' => '3306',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
      'driver' => 'mysql',
    );

Test the connection to the legacy database by running:

    drush ev 'webform_d7_to_d8_test_connection()'

Then you should see:

    Good news: the connection to your legacy database seems OK!

Running the migration
-----

    drush ev 'webform_d7_to_d8()'

You can also run the migration with different options:

    drush ev 'webform_d7_to_d8(["nid" => 123])'
    drush ev 'webform_d7_to_d8(["simulate" => TRUE])'
    drush ev 'webform_d7_to_d8(["nid" => 123, "simulate" => TRUE])'
    drush ev 'webform_d7_to_d8(["max_submissions" => 10])'

Problems with required fields
-----

If webform 123 was created in Drupal 7 with field name as not required, then submissions were created without the name field, and then later on the name field was set to required, we might have issues importing submissions without the name field. You might see something like:

    The following ERRORS occured during import.
    Errors with the following fields (they might be required, for example) for webform 123: name

To fix this, you can run the import like this:

    drush ev 'webform_d7_to_d8(["defaults if required" => [123 => ["name" => "default name"]]])'
