<?php


View::composers(array(
    'Bento\ViewComposer\MenuTodayComposer' => array('admin.menu.partials.today', 'admin.inventory.partials.driver'),
    #'Bento\ViewComposer\DishComposer' => 'admin.dish.crud',
));