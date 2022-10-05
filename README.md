# Wordpress CronJob helper class

I made this to help easily create a WP cronjob. 

## How to use

This is how you can use the helper class.

```PHP
    add_action('wp_loaded', function () {
        CronJobHelper::init(
            '#CRONJOB_HOOK_NAME#', #INTERVAL#,
            function () {
                #YOUR FUNCTION HERE#
            }
        );
    });
```



