<?php
/**
 *  функции для темы (WP)
 */


function isoftpedia_setup()
{

    add_theme_support('title-tag');
/**
 *  это пишется в шаблоне
 * <title><?php bloginfo('name'); ?> |
 *<?php is_home() ? bloginfo('description') : wp_title(''); ?></title>
 */


    /**
     *  логотип сайта и указание размера
     */

    add_theme_support('custom-logo', array(
        'height' => 270,
        'width' => 270,
        'flex-height' => true
    ));
}

/**
     *  скрытие постов учасников от других учасников
     */
// Show only posts and media related to logged in author
add_action('pre_get_posts', 'query_set_only_author' );
function query_set_only_author( $wp_query ) {
    global $current_user;
    if( is_admin() && !current_user_can('edit_others_posts') ) {
        $wp_query->set( 'author', $current_user->ID );
        add_filter('views_edit-post', 'fix_post_counts');
        add_filter('views_upload', 'fix_media_counts');
    }
}

// Fix post counts
function fix_post_counts($views) {
    global $current_user, $wp_query;
    unset($views['mine']);
    $types = array(
        array( 'status' =>  NULL ),
        array( 'status' => 'publish' ),
        array( 'status' => 'draft' ),
        array( 'status' => 'pending' ),
        array( 'status' => 'trash' )
    );
    foreach( $types as $type ) {
        $query = array(
            'author'      => $current_user->ID,
            'post_type'   => 'post',
            'post_status' => $type['status']
        );
        $result = new WP_Query($query);
        if( $type['status'] == NULL ):
            $class = ($wp_query->query_vars['post_status'] == NULL) ? ' class="current"' : '';
            $views['all'] = sprintf(__('<a href="%s"'. $class .'>All <span class="count">(%d)</span></a>', 'all'),
                admin_url('edit.php?post_type=post'),
                $result->found_posts);
        elseif( $type['status'] == 'publish' ):
            $class = ($wp_query->query_vars['post_status'] == 'publish') ? ' class="current"' : '';
            $views['publish'] = sprintf(__('<a href="%s"'. $class .'>Published <span class="count">(%d)</span></a>', 'publish'),
                admin_url('edit.php?post_status=publish&post_type=post'),
                $result->found_posts);
        elseif( $type['status'] == 'draft' ):
            $class = ($wp_query->query_vars['post_status'] == 'draft') ? ' class="current"' : '';
            $views['draft'] = sprintf(__('<a href="%s"'. $class .'>Draft'. ((sizeof($result->posts) > 1) ? "s" : "") .' <span class="count">(%d)</span></a>', 'draft'),
                admin_url('edit.php?post_status=draft&post_type=post'),
                $result->found_posts);
        elseif( $type['status'] == 'pending' ):
            $class = ($wp_query->query_vars['post_status'] == 'pending') ? ' class="current"' : '';
            $views['pending'] = sprintf(__('<a href="%s"'. $class .'>Pending <span class="count">(%d)</span></a>', 'pending'),
                admin_url('edit.php?post_status=pending&post_type=post'),
                $result->found_posts);
        elseif( $type['status'] == 'trash' ):
            $class = ($wp_query->query_vars['post_status'] == 'trash') ? ' class="current"' : '';
            $views['trash'] = sprintf(__('<a href="%s"'. $class .'>Trash <span class="count">(%d)</span></a>', 'trash'),
                admin_url('edit.php?post_status=trash&post_type=post'),
                $result->found_posts);
        endif;
    }
    return $views;
}

// Fix media counts
function fix_media_counts($views) {
    global $wpdb, $current_user, $post_mime_types, $avail_post_mime_types;
    $views = array();
    $count = $wpdb->get_results( "
        SELECT post_mime_type, COUNT( * ) AS num_posts 
        FROM $wpdb->posts 
        WHERE post_type = 'attachment' 
        AND post_author = $current_user->ID 
        AND post_status != 'trash' 
        GROUP BY post_mime_type
    ", ARRAY_A );
    foreach( $count as $row )
        $_num_posts[$row['post_mime_type']] = $row['num_posts'];
    $_total_posts = array_sum($_num_posts);
    $detached = isset( $_REQUEST['detached'] ) || isset( $_REQUEST['find_detached'] );
    if ( !isset( $total_orphans ) )
        $total_orphans = $wpdb->get_var("
            SELECT COUNT( * ) 
            FROM $wpdb->posts 
            WHERE post_type = 'attachment' 
            AND post_author = $current_user->ID 
            AND post_status != 'trash' 
            AND post_parent < 1
        ");
    $matches = wp_match_mime_types(array_keys($post_mime_types), array_keys($_num_posts));
    foreach ( $matches as $type => $reals )
        foreach ( $reals as $real )
            $num_posts[$type] = ( isset( $num_posts[$type] ) ) ? $num_posts[$type] + $_num_posts[$real] : $_num_posts[$real];
    $class = ( empty($_GET['post_mime_type']) && !$detached && !isset($_GET['status']) ) ? ' class="current"' : '';
    $views['all'] = "<a href='upload.php'$class>" . sprintf( __('All <span class="count">(%s)</span>', 'uploaded files' ), number_format_i18n( $_total_posts )) . '</a>';
    foreach ( $post_mime_types as $mime_type => $label ) {
        $class = '';
        if ( !wp_match_mime_types($mime_type, $avail_post_mime_types) )
            continue;
        if ( !empty($_GET['post_mime_type']) && wp_match_mime_types($mime_type, $_GET['post_mime_type']) )
            $class = ' class="current"';
        if ( !empty( $num_posts[$mime_type] ) )
            $views[$mime_type] = "<a href='upload.php?post_mime_type=$mime_type'$class>" . sprintf( translate_nooped_plural( $label[2], $num_posts[$mime_type] ), $num_posts[$mime_type] ) . '</a>';
    }
    $views['detached'] = '<a href="upload.php?detached=1"' . ( $detached ? ' class="current"' : '' ) . '>' . sprintf( __( 'Unattached <span class="count">(%s)</span>', 'detached files' ), $total_orphans ) . '</a>';
    return $views;
}


/**
 *   функция подключение стилей и скриптов (стили / скрипты) везде кроме страници с id13
 */
function название_темы()
{
    if (!is_page(13)) {
        //подключаем стиль
        wp_enqueue_style('название стиля', get_template_directory_uri() . '/libs/min.css', array(), '1.0');
        //подключаем скрипт
        wp_enqueue_script('название скрипта.js', get_template_directory_uri() . '/js/main.js', array(), '1.0', true);
    }
}

add_action('wp_enqueue_scripts', 'название_темы_the_theme_scripts');

/**
 *   функция подключение стилей и скриптов (стили / скрипты) к определенной странице с шаблоном в данном случае id13
 */
function название_темы()
{
    if (is_page(13)) {
        //подключаем стиль
        wp_enqueue_style('название стиля', get_template_directory_uri() . '/libs/min.css', array(), '1.0');
        //подключаем внешние стили без https://
        wp_enqueue_style('название стилей', '//site.com/styles/built.css');
        //подключаем скрипт
        wp_enqueue_script('название скрипта.js', get_template_directory_uri() . '/js/main.js', array(), '1.0', true);
        //подключаем внешние скрипты без https://
        wp_enqueue_script('название скрипта', '//site.com/styles/built.js' );
    }
}

add_action('wp_enqueue_scripts', 'название_темы');

/**
 *   функция подключение стилей и скриптов (стили / скрипты) на все страницы
 */
function название_темы()
{
        //подключаем стиль
        wp_enqueue_style('название стиля', get_template_directory_uri() . '/libs/min.css', array(), '1.0');
        //подключаем скрипт
        wp_enqueue_script('название скрипта.js', get_template_directory_uri() . '/js/main.js', array(), '1.0', true);
}

add_action('wp_print_styles', 'название_темы');



/**
 *   убираем контейнер меню
 */
register_nav_menus(array(
	'main-menu'    => 'Main Menu',    //Название месторасположения меню в шаблоне
	'container' => 'div', // контейнер для меню, по умолчанию 'div', в нашем случае ставим 'nav', пустая строка - нет контейнера
	'container_class' => 'collapse navbar-collapse', // класс для контейнера
	'container_id' => 'collapse-1', // id для контейнера
	'menu_class' => 'nav navbar-nav', // класс для меню
	'menu_id' => '', // id для меню
));

add_filter('wp_nav_menu_args', 'my_wp_nav_menu_args');
function my_wp_nav_menu_args($args)
{
	$args['container'] = false; //убираем контейнер
	return $args;
}

// убираю классы и id элементов меню
function my_remove_all_class_item($classes) {
	$classes = '';
	return $classes;
}
add_filter('nav_menu_css_class', 'my_remove_all_class_item', 10, 2 ); // убираем классы элементов меню
add_filter('nav_menu_item_id', '__return_false'); // убираем id элементов меню

/**
 *   функция тега more... превью записи на главной
 */

add_filter('excerpt_more', function ($more) {
    return '';

});






/**
 ** перевод ярлыков с кирилицы на латиницу транслитом - для урлов
 ** так же экспорт файли тоже переводит (медиа или записи)
 **/

function ctl_sanitize_title($title)
{
    global $wpdb;

    $iso9_table = array(
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Ѓ' => 'G',
        'Ґ' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Є' => 'YE',
        'Ж' => 'ZH', 'З' => 'Z', 'Ѕ' => 'Z', 'И' => 'I', 'Й' => 'J',
        'Ј' => 'J', 'І' => 'I', 'Ї' => 'YI', 'К' => 'K', 'Ќ' => 'K',
        'Л' => 'L', 'Љ' => 'L', 'М' => 'M', 'Н' => 'N', 'Њ' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ў' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'TS',
        'Ч' => 'CH', 'Џ' => 'DH', 'Ш' => 'SH', 'Щ' => 'SHH', 'Ъ' => '',
        'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'ѓ' => 'g',
        'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'є' => 'ye',
        'ж' => 'zh', 'з' => 'z', 'ѕ' => 'z', 'и' => 'i', 'й' => 'j',
        'ј' => 'j', 'і' => 'i', 'ї' => 'yi', 'к' => 'k', 'ќ' => 'k',
        'л' => 'l', 'љ' => 'l', 'м' => 'm', 'н' => 'n', 'њ' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ў' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
        'ч' => 'ch', 'џ' => 'dh', 'ш' => 'sh', 'щ' => 'shh', 'ъ' => '',
        'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    );

    $locale = get_locale();
    switch ($locale) {
        case 'bg_BG':
            $iso9_table['Щ'] = 'SHT';
            $iso9_table['щ'] = 'sht';
            $iso9_table['Ъ'] = 'A';
            $iso9_table['ъ'] = 'a';
            break;
        case 'uk':
            $iso9_table['И'] = 'Y';
            $iso9_table['и'] = 'y';
            break;
    }

    $is_term = false;
    $backtrace = debug_backtrace();
    foreach ($backtrace as $backtrace_entry) {
        if ($backtrace_entry['function'] == 'wp_insert_term') {
            $is_term = true;
            break;
        }
    }

    $term = $is_term ? $wpdb->get_var("SELECT slug FROM {$wpdb->terms} WHERE name = '$title'") : '';

    if (empty($term)) {
        $title = strtr($title, apply_filters('ctl_table', $iso9_table));
        $title = preg_replace("/[^A-Za-z0-9`'_\-\.]/", '-', $title);
    } else {
        $title = $term;
    }

    return strtolower($title);
}

add_filter('sanitize_title', 'ctl_sanitize_title', 9);
add_filter('sanitize_file_name', 'ctl_sanitize_title');

/**
 **** функции которые относятся к выводу постов на главной и странице рубрик
 **/

// вывод превью текста поста - его размеры - текст поста на главной
function new_excerpt_length($length)
{
    return 25; // количество слов для вывода в превью
}

// вывод трех точек после превью текста поста на главной
add_filter('excerpt_length', 'new_excerpt_length');
add_filter('excerpt_more', function ($more) {
    return '...'; // поле где пишутся точки или то что нужно после текста превью поста на главной
});
//// вывод кнопки - ссылки на читать далее
add_filter('excerpt_more', 'new_excerpt_more');
function new_excerpt_more($more)
{
	global $post;
	return '<a href="' . get_permalink($post->ID) . '" class="permalink"> Читать дальше...</a>';
}


// изминения миниатюры для превью поста на главной
if (function_exists('add_theme_support')) {
    add_theme_support('post-thumbnails');
    set_post_thumbnail_size(150, 150); // размер миниатюры поста по умолчанию
}

if (function_exists('add_image_size')) {
    add_image_size('category-thumb', 300, 9999); // 300 в ширину и без ограничения в высоту
    add_image_size('homepage-thumb', 450, 450, true); // Кадрирование изображения
    add_image_size('custom-size', 450, 400, array('center', 'center'));
//	Х_позиция может быть: 'left' 'center' или 'right'.
//	Y_позиция может быть: 'top', 'center' или 'bottom'.
}

// регистрация - объявление своего размера в админке при обрезке на пост
add_filter('image_size_names_choose', 'my_custom_sizes');

function my_custom_sizes($sizes)
{
    return array_merge($sizes, array(
        'custom-size' => 'Размер для поста',
    ));
}

// Исключить страницы из WordPress Search
if (!is_admin()) {
    function wpb_search_filter($query)
    {
        if ($query->is_search) {
            $query->set('post_type', 'post');
        }
        return $query;
    }

    add_filter('pre_get_posts', 'wpb_search_filter');
}

// удаляет тег 'span' в плагине Contact Form 7
add_filter('wpcf7_form_elements', function ($content) {
    $content = preg_replace('/<(span).*?class="\s*(?:.*\s)?wpcf7-form-control-wrap(?:\s[^"]+)?\s*"[^\>]*>(.*)<\/\1>/i', '\2', $content);

    return $content;
});

// удаляет тег 'p' в плагине Contact Form 7
 define( 'WPCF7_AUTOP', false );


// ограничение на загрузку файлов - установка размера

add_filter('upload_size_limit', 'PBP_increase_upload');
function PBP_increase_upload($bytes)
{
    return 90048576; // 1 megabyte
}

// колличество дней на запоминание пароля на защищенные посты и страницы
function true_change_pass_exp($exp)
{
    return time() + 1 * DAY_IN_SECONDS; // 5 дней к примеру
}

add_filter('post_password_expires', 'true_change_pass_exp', 10, 1);


// изменение формы ввода пароля на страницах сайта
function true_new_post_pass_form()
{
    /*
     * в принципе тут нужно обратить внимание на три вещи:
     * 1) куда ссылается форма, а также method=post
     * 2) значение атрибута name поля для ввода - post_password
     * 3) атрибуты size и maxlength поля для ввода должны быть меньше или равны 20 (про длину пароля я писал выше)
     * Во всём остальном у вас полная свобода действий!
     */
    return '<form action="' . esc_url(site_url('wp-login.php?action=postpass', 'login_post')) . '" method="post">
    <p>Данная запись защищена паролем, если у вас нет пароля обратитесь к администратору.</p>
    <p>
	<label for="pwbox-374">
	<input class="input_password_post" name="post_password" type="password" size="20" placeholder="Пароль к записи" maxlength="20" />
	</label>
	<input class="button_password_post" type="submit" name="Submit" value="Разблокировать" />
	</p>
	</form>';
}

add_filter('the_password_form', 'true_new_post_pass_form'); // вешаем функцию на фильтр the_password_form


//цытата запароленной записи - вывод
function true_protected_excerpt_text($excerpt)
{
    if (post_password_required())
        $excerpt = '<em>[Запись заблокирована. Перейдите к прочтению записи для ввода пароля или обратитесь к администратору.]</em>';
    return $excerpt; // если запись не защищена, будет выводиться стандартная цитата
}

add_filter('the_excerpt', 'true_protected_excerpt_text');

/*
 * Небольшая модификация для SQL запроса, получающего посты что бы работало скрытие постов описаное ниже
 */
function true_exclude_pass_posts($where)
{
    global $wpdb;
    return $where .= " AND {$wpdb->posts}.post_password = '' ";
}

/*
 * При помощи этого фильтра определим, на каких именно страницах будет скрывать защищенные посты
 * скрытие запароленных постов на страницах и тд.
 */
function true_where_to_exclude($query)
{
    if (is_front_page()) { // например на главной странице
        add_filter('posts_where', 'true_exclude_pass_posts');
    }
}
     
add_action('pre_get_posts', 'true_where_to_exclude');

      
// чистим от br (удаляем тег </br>)
remove_filter('the_content', 'wpautop');// для контента
//remove_filter( 'the_excerpt', 'wpautop' );// для анонсов
//remove_filter( 'comment_text', 'wpautop' );// для комментарий

/**
 **  регистрация сайт бара в шаблоне
 **/
add_action('widgets_init', 'название_темы_widgets_init');
function название_темы_widgets_init()
{
    register_sidebar(array(
        'name' => __('название сайт бара в админке', 'название_темы'),
        'id' => 'sidebar-1',
        'description' => __('Виджеты в этой области будут показаны на
         всех постах и ​​страницах.', 'название_темы'),
        'class' => 'widget__sidebar', //клас присвоенный виджету
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h2 class="widget__title">',
        'after_title' => '</h2>',
    ));
}
// так подключается данный виджет где угодно в шаблоне страницы
// <?php dynamic_sidebar( 'sidebar-1' ); 

/**
 **  ресайз картинок для увеличения на сайте в попапах - маленькая картинка -> клик-> попап -> большая картинка!
 **/
function resize_imgs()
{
    add_image_size('1920x1080', 1920, 1080, true);
    add_image_size('1920x720', 1920, 720, true);
    add_image_size('900x305', 900, 305, true);
    add_image_size('768x600', 768, 600, true);
    add_image_size('641x415', 641, 415, true);
    add_image_size('638x500', 638, 500, true);
    add_image_size('550x415', 550, 415, true);
    add_image_size('370x368', 370, 368, true);
    add_image_size('356x493', 356, 493, true);
    add_image_size('353x353', 353, 353, true);
    add_image_size('200x283', 200, 283, true);
    add_image_size('362x58', 362, 58, true);
    add_image_size('234x58', 234, 58, true);
    add_image_size('265x37', 265, 37, true);
    add_image_size('181x129', 181, 129, true);
    add_image_size('54x50', 54, 50, true);
}

resize_imgs();


/**
 **  присвоение кастомных класов для тега body на разных страницах и шаблонах страниц - нужно розобраться
 **/
function custom_body_class($wp_classes)
{
    if (is_page('index')) {
        $wp_classes[] = 'index-page';
    }

    if(is_page('commercial')){
        $wp_classes[] = 'commercial-page';
    }

    if(is_page('installation')){
        $wp_classes[] = 'installation-page';
    }
    return $wp_classes;
}

add_filter('body_class', 'custom_body_class', 10, 2);



/**
 **  какието опции для настроек кастомных полей?
 **/
if (function_exists('register_field_group')) {
    // ОПЦИИ ТЕМЫ - Скрипты
    register_field_group(array(
        'id' => 'acf_-options__scripts',
        'title' => 'Скрипты',
        'fields' => array(
            array(
                'key' => 'field_scripts',
                'label' => 'Скрипты',
                'name' => 'scripts',
                'type' => 'repeater',
                'instructions' => 'Вы можете вставить сюда скрипты Яндекс-Метрики, Гугл-Аналитики, сервисов обратной связи и прочие.<br>Вы можете на время отключить скрипт, сняв галочку. Если вы захотите вновь активировать скрипт, вам не придётся его искать.',
                'sub_fields' => array(
                    array(
                        'key' => 'field_script__code',
                        'label' => 'Код',
                        'name' => 'script__code',
                        'type' => 'textarea',
                        'column_width' => '',
                        'default_value' => '',
                        'placeholder' => '',
                        'maxlength' => '',
                        'formatting' => 'text',
                    ),
                    array(
                        'key' => 'field_script__position',
                        'label' => 'Положение',
                        'name' => 'script__position',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => 10,
                            'class' => '',
                            'id' => '',
                        ),
                        'choices' => array(
                            'header' => 'header',
                            'footer' => 'footer',
                        ),
                        'default_value' => array(
                            'header' => 'header',
                        ),
                        'allow_null' => 0,
                        'multiple' => 0,
                        'ui' => 0,
                        'ajax' => 0,
                        'placeholder' => '',
                        'disabled' => 0,
                        'readonly' => 0,
                    ),
                    array(
                        'key' => 'field_script__on',
                        'label' => 'Состояние',
                        'name' => 'script__on',
                        'type' => 'true_false',
                        'column_width' => 8,
                        'message' => '',
                        'default_value' => 1,
                    ),
                ),
                'row_min' => 0,
                'row_limit' => '',
                'layout' => 'table',
                'button_label' => 'Добавить скрипт',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'acf-options-scripts',
                    'order_no' => 0,
                    'group_no' => 0,
                ),
            ),
        ),
        'options' => array(
            'position' => 'normal',
            'layout' => 'no_box',
            'hide_on_screen' => array(),
        ),
        'menu_order' => 0,
    ));
}



if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title' => 'Основные',
        'menu_title' => 'Основные',
        'menu_slug' => 'theme-options',
        'capability' => 'manage_options',
        'parent_slug' => '',
        'position' => '1.1',
        'ico_url' => false,
    ));
    acf_add_options_page(array(
        'page_title' => 'Настройки',
        'menu_title' => 'Настройки',
        'menu_slug' => 'acf-options-common',
        'capability' => 'manage_options',
        'parent_slug' => 'theme-options',
        'position' => false,
        'ico_url' => false,
    ));
    acf_add_options_page(array(
        'page_title' => 'Скрипты',
        'menu_title' => 'Скрипты',
        'menu_slug' => 'acf-options-scripts',
        'capability' => 'manage_options',
        'parent_slug' => 'theme-options',
        'position' => false,
        'ico_url' => false,
    ));
}

if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array(
        'key' => 'group_5a9a73a60a26c',
        'title' => 'Settings',
        'fields' => array(
            array(
                'key' => 'field_5a9a73b8ec80e',
                'label' => 'Facebook',
                'name' => 'facebook',
                'type' => 'link',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
            ),
            array(
                'key' => 'field_5a9a74498c4ea',
                'label' => 'Twitter',
                'name' => 'twitter',
                'type' => 'link',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
            ),
            array(
                'key' => 'field_5a9a74708c4eb',
                'label' => 'Google',
                'name' => 'google',
                'type' => 'link',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
            ),
            array(
                'key' => 'field_5a9a74b98c4ec',
                'label' => 'Address',
                'name' => 'address',
                'type' => 'group',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'layout' => 'block',
                'sub_fields' => array(
                    array(
                        'key' => 'field_5a9a74d38c4ed',
                        'label' => 'Country, City',
                        'name' => 'country_city',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_5a9a75128c4ee',
                        'label' => 'Postcode, street',
                        'name' => 'postcode_street',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_5a9a75678c4ef',
                        'label' => 'Phone',
                        'name' => 'phone',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'acf-options-common',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => 1,
        'description' => '',
    ));

endif;



































