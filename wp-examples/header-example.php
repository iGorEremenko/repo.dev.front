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
                <svg>
                    <use xlink:href="<?php echo get_template_directory_uri()?>/assets/img/sprite-inline.svg#company-logo"></use>
                </svg>
            <nav class="menu-js">
                <ul class="main-menu">
                    <li <?=(get_permalink() == get_page_link(91)) ? 'class="active"' : '';?>><a href="<?php echo get_page_link(91) ?>"><?php echo get_the_title(91);?></a></li>
                    <li <?=(get_permalink() == get_page_link(122)) ? 'class="active"' : '';?> ><a href="<?php echo get_page_link(122) ?>"><?php echo get_the_title(122);?></a></li>
                    <li <?=(get_permalink() == get_page_link(158)) ? 'class="active"' : '';?> ><a href="<?php echo get_page_link(158) ?>"><?php echo get_the_title(158) ?></a></li>
                    <li <?=(get_permalink() == get_page_link(173)) ? 'class="active"' : '';?> ><a href="<?php echo get_page_link(173) ?>"><?php echo get_the_title(173) ?></a></li>
                </ul>
            </nav>
</header>
