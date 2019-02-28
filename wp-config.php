<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'Artisana');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '123456');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'up8TWXacp|CT8+4*-:s=j|Bl}fjKi+#v>`~#(tiovJ;DL9GeijQ2BM;>in#|!F[.');
define('SECURE_AUTH_KEY',  '`,m30Z?a!BdM?ic3`=,t3{n>UN.K:GrR~_G|jXu1#7f2iZX5jE9%dFAn{+1t|35R');
define('LOGGED_IN_KEY',    '|fJ3 <+n!=?Wu|<+XD3s2F]}vmFM7zkH{,TA.z/h/[rWH~4T`Ipuc.CB41LC#8,_');
define('NONCE_KEY',        ' x!.[FA%3`8f)_A{]6<GaK-(i@Ub:%T!N`!D9pVD05ce6;2g8X-e{-SrxvvD5TI}');
define('AUTH_SALT',        'puE$,J=1 9hk;qQ/O|3Jhmsy+aP rKBWf9IXd7%+ rTZhJM;:}(;N D;_OZ<35U}');
define('SECURE_AUTH_SALT', '{qwbyZbD3/a6/z&SN}o3$&WOh!Wj8@FtyiQtT/X54OBTbg2uXT;5I2F]:$>q|co,');
define('LOGGED_IN_SALT',   '?`pXb((1kan39bqw%.WMk8=gh`]>QfH-ER9sTLhDN3(jsU5GkJ)(UTvB!ik{a?|u');
define('NONCE_SALT',       'aV}rX.]0VARXE0 UE@bk3Au{b8>@amci-,*$e,r$ug8tE-H@&yd$X/|-eFbA$-w;');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
define('FS_METHOD', 'direct');

