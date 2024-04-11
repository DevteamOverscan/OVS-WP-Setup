<?php
/**
 *
 * @package OVS
 * @author Clément Vacheron
 * @link https://www.overscan.com
 */

if (!defined('ABSPATH')) {
    exit;
}

function htaccessBase()
{

    $home_url = get_home_url();
    $parsed_url = parse_url($home_url);
    $domain = $parsed_url['host'];

    $rules = '
#Désactive la Méthode de suivi HTTP
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_METHOD} ^TRACE
RewriteRule .* - [F]
</IfModule>

# Protéger les fichiers .htaccess et .htpasswds
<Files ~ "^.*\.([Hh][Tt][AaPp])">
order allow,deny
deny from all
satisfy all
</Files>

# Protéger le fichier wp-config.php
<files wp-config.php>
order allow,deny
deny from all
</files>

<FilesMatch "^(xmlrpc\.php)">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# BEGIN Security access files

#bloc l\'accès au dossier include et wp-admin et ce qui se trouve dedans
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^wp-admin/includes/ - [F,L]
RewriteRule !^wp-includes/ - [S=3]
RewriteRule ^wp-includes/[^/]+\.php$ - [F,L]
RewriteRule ^wp-includes/js/tinymce/langs/.+\.php - [F,L]
RewriteRule ^wp-includes/theme-compat/ - [F,L]
</IfModule>

#Empeche la navigations dans les repertoires
Options All -Indexes
# END Security access files

# Éviter les warnings coté client
<IfModule mod_rewrite.c>
RewriteCond %{SERVER_PORT} !^443
RewriteRule ^http://tudsol.overscan.io/%{REQUEST_URI} [R=301,L]
</IfModule>

# Désactiver l\'énumération des auteurs
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} ^/$
    RewriteCond %{QUERY_STRING} ^/?author=([0-9]*)
    RewriteRule .* - [R=403,L]
</IfModule>

# BEGIN désactivation du hotlinking
<IfModule mod_rewrite.c>
RewriteEngine on
RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?'. $domain .' [NC]
RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?'. $domain .' [NC]
RewriteRule \.(jpg|jpeg|png|gif)$ http://i.imgur.com/g7ptdBB.png [NC,R,L]
</IfModule>
# END désactivation du hotlinking

# BEGIN Compatibility IE
#interprétation des fichiers JavaScript par les versions d\'Internet Explorer
<IfModule mod_headers.c>
Header set X-UA-Compatible "IE=Edge,chrome=1"
# mod_headers can\'t match by content-type, but we don\'t want to send this header on *everything*...
<FilesMatch ".(js|css|gif|png|jpe?g|pdf|xml|oga|ogg|m4a|ogv|mp4|m4v|webm|svg|svgz|eot|ttf|otf|woff|ico|webp|appcache|manifest|htc|crx|xpi|safariextz|vcf)$" >
Header unset X-UA-Compatible
</FilesMatch>
</IfModule>
# END Compatibility IE

# BEGIN Remove Etag
<IfModule mod_headers.c>
Header unset ETag
FileETag none
</IfModule>
# END Remove Etag

#Pour les navigateurs incompatibles
BrowserMatch ^Mozilla/4 gzip-only-text/html
BrowserMatch ^Mozilla/4\.0[678] no-gzip
BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
BrowserMatch \bMSI[E] !no-gzip !gzip-only-text/html
#ne pas mettre en cache si ces fichiers le sont déjà
SetEnvIfNoCase Request_URI \.(?:gif|jpe?g|png)$ no-gzip

<IfModule mod_include.c>
<FilesMatch "^\.combined\.js$">
Options +Includes
AddOutputFilterByType INCLUDES application/javascript application/json
SetOutputFilter INCLUDES
</FilesMatch>
<FilesMatch "^\.combined\.css$">
Options +Includes
AddOutputFilterByType INCLUDES text/css
SetOutputFilter INCLUDES
</FilesMatch>
</IfModule>

# BEGIN STOP SPAM ON WORDPRESS BLOG
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_METHOD} POST
RewriteCond %{REQUEST_URI} .wp-comments-post.php*
RewriteCond %{HTTP_REFERER} !.*'.$domain.'.* [OR]
RewriteCond %{HTTP_USER_AGENT} ^$
RewriteRule (.*) ^'.$home_url.'/$ [R=301,L]  
</IfModule>
# END STOP SPAM ON WORDPRESS BLOG
AddDefaultCharset utf-8
AddCharset utf-8 .html .css .js .xml .json .rss .atom
DefaultLanguage "fr"

# BEGIN HttpHeaders
<IfModule mod_headers.c>
#Renforcement http headers
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
Header set X-Frame-Options "sameorigin"
Header set X-XSS-Protection "1; mode=block"
Header set X-Content-Type-Options "nosniff"
Header set Referrer-Policy "strict-origin-when-cross-origin"
Header set Permissions-Policy "all=()"
Header set Content-Security-Policy "default-src \'self\';"
Header set Content-Security-Policy "font-src \'self\' fonts.gstatic.com data:;"
Header set Content-Security-Policy "style-src \'self\' \'unsafe-inline\' cdnjs.cloudflare.com;"
Header set Content-Security-Policy "script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' _.facebook.net tag.aticdn.net _.cloudflare.com _.google.com unpkg.com _.gstatic.com;"
Header set Content-Security-Policy "style-src-elem \'self\' \'unsafe-inline\' unpkg.com fonts.googleapis.com;"
Header set Content-Security-Policy "frame-src \'self\';"
Header set Content-Security-Policy "img-src \'self\' data:;"
Header set Content-Security-Policy "connect-src \'self\' https://*.google-analytics.com https://*.lottiefiles.com;"
Header set Content-Security-Policy "frame-ancestors \'none\';"
Header set Cache-Control "max-age=86400, no-cache, proxy-revalidate, must-revalidate"
</IfModule>

# BEGIN Cache
<FilesMatch "index\.(html|htm)$">
<ifModule mod_headers.c>
Header set Cache-Control "max-age=0, no-cache, no-store, must-revalidate"
Header set Expires "Mon, 29 Oct 1923 20:30:00 GMT"
</ifModule>
</FilesMatch>
<FilesMatch "sitemaps\.(xml|xsl)$">
<ifModule mod_headers.c>
Header set Cache-Control "max-age=0, no-cache, no-store, must-revalidate"
Header set Expires "Mon, 29 Oct 1923 20:30:00 GMT"
</ifModule>
</FilesMatch>

<ifModule mod_headers.c>
<filesMatch "\.(ico|jpe?g|png|gif|swf)$">
Header set Cache-Control "public"
</filesMatch>
<filesMatch "\.(css)$">
Header set Cache-Control "public"
</filesMatch>
<filesMatch "\.(js)$">
Header set Cache-Control "private"
</filesMatch>
<filesMatch "\.(x?html?|php)$">
Header set Cache-Control "private, must-revalidate"
</filesMatch>
</ifModule>
# END Cache

# BEGIN Cache Advanced
<IfModule mod_mime.c>
    AddType text/css .css
    AddType text/x-component .htc
    AddType application/x-javascript .js
    AddType application/javascript .js2
    AddType text/javascript .js3
    AddType text/x-js .js4
    AddType video/asf .asf .asx .wax .wmv .wmx
    AddType video/avi .avi
    AddType image/avif .avif
    AddType image/avif-sequence .avifs
    AddType image/bmp .bmp
    AddType application/java .class
    AddType video/divx .divx
    AddType application/msword .doc .docx
    AddType application/vnd.ms-fontobject .eot
    AddType application/x-msdownload .exe
    AddType image/gif .gif
    AddType application/x-gzip .gz .gzip
    AddType image/x-icon .ico
    AddType image/jpeg .jpg .jpeg .jpe
    AddType image/webp .webp
    AddType application/json .json
    AddType application/vnd.ms-access .mdb
    AddType audio/midi .mid .midi
    AddType video/quicktime .mov .qt
    AddType audio/mpeg .mp3 .m4a
    AddType video/mp4 .mp4 .m4v
    AddType video/mpeg .mpeg .mpg .mpe
    AddType video/webm .webm
    AddType application/vnd.ms-project .mpp
    AddType application/x-font-otf .otf
    AddType application/vnd.ms-opentype ._otf
    AddType application/vnd.oasis.opendocument.database .odb
    AddType application/vnd.oasis.opendocument.chart .odc
    AddType application/vnd.oasis.opendocument.formula .odf
    AddType application/vnd.oasis.opendocument.graphics .odg
    AddType application/vnd.oasis.opendocument.presentation .odp
    AddType application/vnd.oasis.opendocument.spreadsheet .ods
    AddType application/vnd.oasis.opendocument.text .odt
    AddType audio/ogg .ogg
    AddType video/ogg .ogv
    AddType application/pdf .pdf
    AddType image/png .png
    AddType application/vnd.ms-powerpoint .pot .pps .ppt .pptx
    AddType audio/x-realaudio .ra .ram
    AddType image/svg+xml .svg .svgz
    AddType application/x-shockwave-flash .swf
    AddType application/x-tar .tar
    AddType image/tiff .tif .tiff
    AddType application/x-font-ttf .ttf .ttc
    AddType application/vnd.ms-opentype ._ttf
    AddType audio/wav .wav
    AddType audio/wma .wma
    AddType application/vnd.ms-write .wri
    AddType application/font-woff .woff
    AddType application/font-woff2 .woff2
    AddType application/vnd.ms-excel .xla .xls .xlsx .xlt .xlw
    AddType application/zip .zip
</IfModule>
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/html "access plus 1 day"
    ExpiresByType text/css A31536000
    ExpiresByType text/x-component A31536000
    ExpiresByType application/x-javascript A31536000
    ExpiresByType application/javascript A31536000
    ExpiresByType text/javascript A31536000
    ExpiresByType text/x-js A31536000
    ExpiresByType video/asf A31536000
    ExpiresByType video/avi A31536000
    ExpiresByType image/avif A31536000
    ExpiresByType image/avif-sequence A31536000
    ExpiresByType image/bmp A31536000
    ExpiresByType application/java A31536000
    ExpiresByType video/divx A31536000
    ExpiresByType application/msword A31536000
    ExpiresByType application/vnd.ms-fontobject A31536000
    ExpiresByType application/x-msdownload A31536000
    ExpiresByType image/gif A31536000
    ExpiresByType application/x-gzip A31536000
    ExpiresByType image/x-icon A31536000
    ExpiresByType image/jpeg A31536000
    ExpiresByType image/webp A31536000
    ExpiresByType application/json A31536000
    ExpiresByType application/vnd.ms-access A31536000
    ExpiresByType audio/midi A31536000
    ExpiresByType video/quicktime A31536000
    ExpiresByType audio/mpeg A31536000
    ExpiresByType video/mp4 A31536000
    ExpiresByType video/mpeg A31536000
    ExpiresByType video/webm A31536000
    ExpiresByType application/vnd.ms-project A31536000
    ExpiresByType application/x-font-otf A31536000
    ExpiresByType application/vnd.ms-opentype A31536000
    ExpiresByType application/vnd.oasis.opendocument.database A31536000
    ExpiresByType application/vnd.oasis.opendocument.chart A31536000
    ExpiresByType application/vnd.oasis.opendocument.formula A31536000
    ExpiresByType application/vnd.oasis.opendocument.graphics A31536000
    ExpiresByType application/vnd.oasis.opendocument.presentation A31536000
    ExpiresByType application/vnd.oasis.opendocument.spreadsheet A31536000
    ExpiresByType application/vnd.oasis.opendocument.text A31536000
    ExpiresByType audio/ogg A31536000
    ExpiresByType video/ogg A31536000
    ExpiresByType application/pdf A31536000
    ExpiresByType image/png A31536000
    ExpiresByType application/vnd.ms-powerpoint A31536000
    ExpiresByType audio/x-realaudio A31536000
    ExpiresByType image/svg+xml A31536000
    ExpiresByType application/x-shockwave-flash A31536000
    ExpiresByType application/x-tar A31536000
    ExpiresByType image/tiff A31536000
    ExpiresByType application/x-font-ttf A31536000
    ExpiresByType application/vnd.ms-opentype A31536000
    ExpiresByType audio/wav A31536000
    ExpiresByType audio/wma A31536000
    ExpiresByType application/vnd.ms-write A31536000
    ExpiresByType application/font-woff A31536000
    ExpiresByType application/font-woff2 A31536000
    ExpiresByType application/vnd.ms-excel A31536000
    ExpiresByType application/zip A31536000
</IfModule>
<IfModule mod_deflate.c>
    <IfModule mod_filter.c>
        AddOutputFilterByType DEFLATE text/css text/x-component application/x-javascript application/javascript text/javascript text/x-js text/html text/richtext text/plain text/xsd text/xsl text/xml image/bmp application/java application/msword application/vnd.ms-fontobject application/x-msdownload image/x-icon application/json application/vnd.ms-access video/webm application/vnd.ms-project application/x-font-otf application/vnd.ms-opentype application/vnd.oasis.opendocument.database application/vnd.oasis.opendocument.chart application/vnd.oasis.opendocument.formula application/vnd.oasis.opendocument.graphics application/vnd.oasis.opendocument.presentation application/vnd.oasis.opendocument.spreadsheet application/vnd.oasis.opendocument.text audio/ogg application/pdf application/vnd.ms-powerpoint image/svg+xml application/x-shockwave-flash image/tiff application/x-font-ttf application/vnd.ms-opentype audio/wav application/vnd.ms-write application/font-woff application/font-woff2 application/vnd.ms-excel
    <IfModule mod_mime.c>
        # DEFLATE by extension
        AddOutputFilter DEFLATE js css htm html xml
    </IfModule>
    </IfModule>
</IfModule>
<FilesMatch "\.(css|htc|less|js|js2|js3|js4|CSS|HTC|LESS|JS|JS2|JS3|JS4)$">
    FileETag MTime Size
    <IfModule mod_headers.c>
        Header unset Set-Cookie
    </IfModule>
</FilesMatch>
<FilesMatch "\.(html|htm|rtf|rtx|txt|xsd|xsl|xml|HTML|HTM|RTF|RTX|TXT|XSD|XSL|XML)$">
    FileETag MTime Size
</FilesMatch>
<FilesMatch "\.(asf|asx|wax|wmv|wmx|avi|avif|avifs|bmp|class|divx|doc|docx|eot|exe|gif|gz|gzip|ico|jpg|jpeg|jpe|webp|json|mdb|mid|midi|mov|qt|mp3|m4a|mp4|m4v|mpeg|mpg|mpe|webm|mpp|otf|_otf|odb|odc|odf|odg|odp|ods|odt|ogg|ogv|pdf|png|pot|pps|ppt|pptx|ra|ram|svg|svgz|swf|tar|tif|tiff|ttf|ttc|_ttf|wav|wma|wri|woff|woff2|xla|xls|xlsx|xlt|xlw|zip|ASF|ASX|WAX|WMV|WMX|AVI|AVIF|AVIFS|BMP|CLASS|DIVX|DOC|DOCX|EOT|EXE|GIF|GZ|GZIP|ICO|JPG|JPEG|JPE|WEBP|JSON|MDB|MID|MIDI|MOV|QT|MP3|M4A|MP4|M4V|MPEG|MPG|MPE|WEBM|MPP|OTF|_OTF|ODB|ODC|ODF|ODG|ODP|ODS|ODT|OGG|OGV|PDF|PNG|POT|PPS|PPT|PPTX|RA|RAM|SVG|SVGZ|SWF|TAR|TIF|TIFF|TTF|TTC|_TTF|WAV|WMA|WRI|WOFF|WOFF2|XLA|XLS|XLSX|XLT|XLW|ZIP)$">
    FileETag MTime Size
    <IfModule mod_headers.c>
        Header unset Set-Cookie
    </IfModule>
</FilesMatch>
<IfModule mod_headers.c>
    Header set Referrer-Policy "no-referrer-when-downgrade"
</IfModule>
# END Cache Advanced
';
    $htaccessFile = ABSPATH . '/.htaccess';

    $results = file_put_contents($htaccessFile, $rules, FILE_APPEND | LOCK_EX);
    return $results;

}
