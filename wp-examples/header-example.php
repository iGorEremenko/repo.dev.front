<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1 user-scalable=no">
    <title><?php the_title();?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); /*относится к функции кастомных слассов для тега body*/ ?>>
<header class="header">
                <svg><!--подключаем векторное изображение svg из темы-->
                    <use xlink:href="<?php echo get_template_directory_uri()/*получаем ссылку к теме а дальше строем относительно темы*/?>/assets/img/sprite-inline.svg#company-logo"></use>
                </svg>
            <nav class="menu-js">
                <ul class="main-menu"><!--подключение меню которое не управляется из админки - жестко забивается только тут-->
                    <li <?=(get_permalink() == get_page_link(91)) ? 'class="active"' : '';/*вешаем класс эктив на элемент меню соответствующий страницк на которой мы наодимся (обращаемся к странице по id)*/?>>
                        <a href="<?php echo get_page_link(91)/*выводим урл на страницу по (id)*/ ?>"><?php echo get_the_title(91);/*выводим название страницы из админки по (id)*/?></a></li>
                </ul>
            </nav>
</header>
