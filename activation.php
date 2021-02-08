<?php

register_activation_hook(__FILE__, 'QS_PAG_plugin_activate');

function QS_PAG_plugin_activate()
{
    if (!is_woocommerce_activated()) {
        $message = '¡Necesitas instalar y habilitar WooCommerce!';
    }

    $message = 'El plugin se ha instalado satisfactoriamente';

    return $message;
}
