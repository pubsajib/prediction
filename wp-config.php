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
define('DB_NAME', 'prediction');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '32bit.PNG');

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
define('AUTH_KEY',         '= 3doy3YN;WAnkcuAzwS=e,_.&=[tviM}heCj>r.,Qj?8vmB!2;sPErmF[q!>WZ`');
define('SECURE_AUTH_KEY',  'lvT %j~&-&{qr6Xk@]X7J8E#M91F,-.QxuI0$beHKn-Ua$prKh~q=Ha;epcUkr+&');
define('LOGGED_IN_KEY',    '#F#eES|2k 3I2+02W%),Ew)frK.Oyz~e-2NJ25gY(n.t_TGq)*J6#c1)+?m8DJ+t');
define('NONCE_KEY',        'oRC-v+3z6wZwNA~Ux/YZR+540Rs 5 .^|0#MaE*g1zhrbNX3k#X?U)&f<xy` ?kq');
define('AUTH_SALT',        'ztZS*Vg4T@M+_=,*O=Vr3Gt!R3zPj;>;z NW9&6i&Zf16kQgup~s7.oMLJ.+zMPE');
define('SECURE_AUTH_SALT', 'PZo/+_{;.C>L>1H@#.|$_>Rb4eX}F23lGki>thbP(N7Dx^@/~0u|zIKj$Cr%8;(B');
define('LOGGED_IN_SALT',   '.)S_ B%Pi4+b17b:7oIh}wj6;hOakWh(UpB P(D=1O,; gmb.IVd&zSe_17{)Fn3');
define('NONCE_SALT',       ':sY^*:6s.e?4+|(N2J=:W {Tz%yM4<bW?(8@o;tP307o(zbjvQI~~z#>kQ8FVa[0');

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
// define('WP_DEBUG', true);
// define('WP_DEBUG_LOG', true);
// define('WP_DEBUG_DISPLAY', 1);
// @ini_set('display_errors', 1);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
