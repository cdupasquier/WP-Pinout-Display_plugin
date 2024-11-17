<?php
/*
Plugin Name: Pinout Display
Description: Plugin pour afficher un schéma de broches interactif avec des détails pour chaque broche.
Version: 1.0
Author: Christophe Dupasquier
*/

if (!defined('ABSPATH')) {
    exit; // Sécurisation du fichier pour empêcher l'accès direct
}

// Fonction pour afficher le shortcode
function pinout_display_shortcode()
{
    ob_start(); // Démarre la mise en tampon de sortie
?>
    <div id="pinout-grid">
        <!-- Les broches seront générées ici -->
        <div class="pin" data-pin-id="1">Pin 1</div>
        <div class="pin" data-pin-id="2">Pin 2</div>
        <div class="pin" data-pin-id="3">Pin 3</div>
        <!-- Ajoute plus de broches selon tes besoins -->
    </div>
    <div id="pinout-details" style="display: none;">
        <!-- Les détails des broches apparaîtront ici -->
    </div>
<?php
    return ob_get_clean(); // Renvoie le contenu mis en tampon
}
add_shortcode('pinout_display', 'pinout_display_shortcode');

// Fonction pour charger les scripts et styles
function pinout_enqueue_assets()
{
    wp_enqueue_style('pinout-style', plugin_dir_url(__FILE__) . 'assets/css/pinout-style.css');
    wp_enqueue_script('pinout-script', plugin_dir_url(__FILE__) . 'assets/js/pinout-script.js', array('jquery'), null, true);
    // Ajout de l'objet AJAX pour l'URL dans les scripts
    wp_localize_script('pinout-script', 'pinout_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'pinout_enqueue_assets');

// Fonction pour créer la table lors de l'activation du plugin
function pinout_create_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'pinout_pins'; // Nom de la table
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        pin_number int NOT NULL,
        pin_name varchar(255) NOT NULL,
        pin_description text NOT NULL,
        cx int NOT NULL,  -- Position X pour le SVG
        cy int NOT NULL,  -- Position Y pour le SVG
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'pinout_create_table');

// Fonction pour insérer des broches par défaut dans la base de données
// Fonction pour insérer des broches par défaut dans la base de données
function pinout_insert_default_pins()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'pinout_pins';

    // Vérification si des données existent déjà
    $row = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // Si aucune donnée, insertion initiale
    if ($row == 0) {
        // Insertion des données de base (numéro de broche, nom, description, couleur, coordonnées cx et cy)
        $pins = array(
            array(1, 1, '3.3V Power', 'Tous les modèles de Raspberry Pi depuis le B+ peuvent fournir jusqu\'à 500 mA sur les broches 3,3 V, grâce à un régulateur à découpage. Dans certains cas, il peut être possible de tirer plus, mais en raison du manque de documentation et de tests sur les limites réelles, 500 mA est donné comme règle générale.<br><br>La broche d\'alimentation 3,3 V sur les premiers Raspberry Pi avait un courant maximum disponible de seulement 50 mA.<br><br>Il est recommandé d\'utiliser l\'alimentation 5 V couplée à un régulateur 3,3 V pour alimenter les projets en 3,3 V.<br><br>Le blog <a href=\"https://raspberrypise.tumblr.com/post/144555785379/exploring-the-33v-power-rail\" target=\"_blank\" rel=\"noopener\">Piversify</a> en anglais, propose une exploration du rail d\'alimentation 3,3 V sur le Raspberry Pi B+.', 'orange', 150, 100),
            array(2, 2, '5V Power', 'Les broches d\'alimentation 5 V sont connectées directement à l\'entrée d\'alimentation du Raspberry Pi et peuvent fournir l\'intégralité du courant d\'alimentation de votre adaptateur secteur, moins ce qui est utilisé par le Pi lui-même.<br><br>Avec une alimentation de bonne qualité, comme l\'adaptateur officiel du Pi, vous pouvez vous attendre à tirer environ 1,5 A. Cela varie selon le modèle de Pi et l\'adaptateur utilisé. Les appareils nécessitant un courant élevé, tels que les panneaux LED, les longues bandes LED ou les moteurs, devraient utiliser une alimentation externe.', 'red', 150, 100),
            array(3, 3, 'GPIO 2 (Données I2C)', '<table><tbody><tr><th></th><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr><tr><td>GPIO 2</td><td>I2C1 SDA</td><td>SMI SA3</td><td>DPI VSYNC</td><td>AVEOUT VSYNC</td><td>AVEIN VSYNC</td></tr></tbody></table><ul><li>Broche physique / Sur la carte : 3</li><li>Broche GPIO / BCM : 2</li><li>Broche Wiring Pi : 8</li><li>Broche GPIO / BCM : 0 (très ancien) Pi sur Rev 1</li></ul>SDA comprend une résistance de rappel fixe de 1,8 kΩ vers 3,3 V, ce qui signifie que cette broche n\'est pas adaptée à une utilisation en tant qu\'E/S générale où aucune résistance de rappel n\'est souhaitée.', 'blue', 150, 140),
            array(4, 4, '5V Power', 'Les broches d\'alimentation 5 V sont connectées directement à l\'entrée d\'alimentation du Raspberry Pi et peuvent fournir l\'intégralité du courant d\'alimentation de votre adaptateur secteur, moins ce qui est utilisé par le Pi lui-même.<br><br>Avec une alimentation de bonne qualité, comme l\'adaptateur officiel du Pi, vous pouvez vous attendre à tirer environ 1,5 A. Cela varie selon le modèle de Pi et l\'adaptateur utilisé. Les appareils nécessitant un courant élevé, tels que les panneaux LED, les longues bandes LED ou les moteurs, devraient utiliser une alimentation externe.', 'red', 300, 140),
            array(5, 5, 'GPIO 3 (Horloge I2C)', '<table style="width: 100%;border-collapse: collapse;margin-bottom: 10px"><thead><tr><th style="border: 1px solid #ddd;padding: 8px">Alt0</th><th style="border: 1px solid #ddd;padding: 8px">Alt1</th><th style="border: 1px solid #ddd;padding: 8px">Alt2</th><th style="border: 1px solid #ddd;padding: 8px">Alt3</th><th style="border: 1px solid #ddd;padding: 8px">Alt4</th><th style="border: 1px solid #ddd;padding: 8px">Alt5</th></tr></thead><tbody><tr><td style="border: 1px solid #ddd;padding: 8px">I2C1 SCL</td><td style="border: 1px solid #ddd;padding: 8px">SMI SA2</td><td style="border: 1px solid #ddd;padding: 8px">DPI HSYNC</td><td style="border: 1px solid #ddd;padding: 8px">AVEOUT HSYNC</td><td style="border: 1px solid #ddd;padding: 8px">AVEIN HSYNC</td></tr></tbody></table><ul><li>Broche physique / Sur la carte : 5</li><li>Broche GPIO / BCM : 3</li><li>Broche Wiring Pi : 9</li><li>Broche GPIO / BCM : 1 (très ancien) Pi sur Rev 1</li></ul>SCL (Horloge I2C1) est l\'une des broches I2C sur le Pi.<br><br>SCL comprend une résistance de rappel fixe de 1,8 kΩ vers 3,3 V, ce qui signifie que cette broche n\'est pas adaptée à une utilisation en tant qu\'E/S générale où aucune résistance de rappel n\'est souhaitée.', 'blue', 150, 180),
            array(6, 6, 'Masse', 'Les broches de masse sur le Raspberry Pi sont toutes électriquement connectées, donc peu importe laquelle tu utilises si tu câbles une alimentation en tension.<br><br>En général, celle qui est la plus pratique ou la plus proche des autres connexions est plus soignée et plus facile à utiliser, ou alternativement celle qui est la plus proche de la broche d\'alimentation que tu utilises.<br><br>Par exemple, il est judicieux d\'utiliser la broche physique 17 pour le 3,3 V et la broche physique 25 pour la masse lorsque tu utilises les connexions SPI, car elles sont juste à côté des broches importantes pour le SPI0.', 'black', 300, 180),
            array(7, 7, 'GPIO 4', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>GPCLK0</td><td>SMI SA1</td><td>DPI D0</td><td>AVEOUT VID0</td><td>AVEIN VID0</td><td>JTAG TDI</td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 7</li><li>Broche GPIO / BCM : 4</li><li>Broche Wiring Pi : 7</li></ul>', 'pink', 150, 220),
            array(8, 8, 'GPIO 14 (Transmission UART)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>UART0 TXD</td><td>SMI SD6</td><td>DSI D10</td><td>AVEOUT VID10</td><td>AVEIN VID10</td><td>UART1 TXD</td></tr></tbody></table><ul><li>Broche physique / Sur la carte : 8</li><li>Broche GPIO / BCM : 14</li><li>Broche Wiring Pi : 15</li></ul>Cette broche fait également office de broche de transmission UART, TX. Elle est également couramment connue sous le nom de "Série" et, par défaut, elle émettra un affichage de la console de votre Pi que, avec un câble série approprié, vous pouvez utiliser pour contrôler votre Pi via la ligne de commande.<br><br>Les broches UART sont utiles pour configurer un Pi "sans tête" (un Pi sans écran) et pour le connecter à un réseau.<br><br>UART peut être utilisé pour communiquer avec des modules GPS série ou des capteurs tels que le PM5003, mais vous devez d\'abord vous assurer de désactiver la console série dans raspi-config.<br><br>Sur le Pi 3 et 4, l\'UART est, par défaut, utilisé pour le Bluetooth, et vous devrez peut-être ajouter "dtoverlay=miniuart-bt" à "/boot/config.txt" pour obtenir une connexion stable.', 'green', 300, 220),
            array(9, 9, 'Masse', 'Les broches de masse sur le Raspberry Pi sont toutes électriquement connectées, donc peu importe laquelle tu utilises si tu câbles une alimentation en tension.<br><br>En général, celle qui est la plus pratique ou la plus proche des autres connexions est plus soignée et plus facile à utiliser, ou alternativement celle qui est la plus proche de la broche d\'alimentation que tu utilises.<br><br>Par exemple, il est judicieux d\'utiliser la broche physique 17 pour le 3,3 V et la broche physique 25 pour la masse lorsque tu utilises les connexions SPI, car elles sont juste à côté des broches importantes pour le SPI0.', 'black', 300, 260),
            array(10, 10, 'GPIO 15 (Réception UART)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>UART0 RXD</td><td>SMI SD7</td><td>DPI D11</td><td>AVEOUT VID11</td><td>AVEIN VID11</td><td>UART1 RXD</td></tr></tbody></table><ul><li>Broche physique / Sur la carte : 10</li><li>Broche GPIO / BCM : 15</li><li>Broche Wiring Pi : 16</li></ul>Cette broche fait également office de broche de réception UART, RX. Elle est également couramment connue sous le nom de "Série" et, par défaut, elle émettra un affichage de la console de votre Pi que, avec un câble série approprié, vous pouvez utiliser pour contrôler votre Pi via la ligne de commande.<br><br>Les broches UART sont utiles pour configurer un Pi "sans tête" (un Pi sans écran) et pour le connecter à un réseau.<br><br>UART peut être utilisé pour communiquer avec des modules GPS série ou des capteurs tels que le PM5003, mais vous devez d\'abord vous assurer de désactiver la console série dans raspi-config.<br><br>Sur le Pi 3 et 4, l\'UART est, par défaut, utilisé pour le Bluetooth, et vous devrez peut-être ajouter "dtoverlay=miniuart-bt" à "/boot/config.txt" pour obtenir une connexion stable.', 'pink', 300, 260),
            array(11, 11, 'GPIO 17', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>FL1</td><td>SMI SD9</td><td>DPI D13</td><td>UART0 RTS</td><td>SPI1 CE1</td><td>UART1 RTS</td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 11</li><li>Broche GPIO / BCM : 17</li><li>Broche Wiring Pi : 0</li></ul>', 'green', 150, 300),
            array(12, 12, 'GPIO 18 (Horloge PCM)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>PCM CLK</td><td>SMI SD10</td><td>DPI D14</td><td>I2CSL SDA / MOSI</td><td>SPI1 CE0</td><td>PWM0</td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 12</li><li>Broche GPIO / BCM : 18</li><li>Broche Wiring Pi : 1</li></ul>La broche GPIO 18 est utilisée par le PCM pour fournir un signal d\'horloge à un appareil audio externe, tel qu\'une puce DAC.<br><br>La sortie PWM0 de la GPIO 18 est particulièrement utile, en combinaison avec quelques astuces de accès mémoire direct rapide, pour piloter des appareils avec des temporisations très spécifiques. Les LED WS2812 sur le Unicorn HAT en sont un bon exemple en action.', 'turquoise', 300, 300),
            array(13, 13, 'GPIO 27', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>SD0 DAT3</td><td>TE1</td><td>DPI D23</td><td>SD1 DAT3</td><td>JTAG TMS</td><td></td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 13</li><li>Broche GPIO / BCM : 27</li><li>Broche Wiring Pi : 2</li><li>Broche GPIO / BCM : 21 (très ancien) Pi sur Rev 1</li></ul>', 'green', 150, 340),
            array(14, 14, 'Masse', 'Les broches de masse sur le Raspberry Pi sont toutes électriquement connectées, donc peu importe laquelle tu utilises si tu câbles une alimentation en tension.<br><br>En général, celle qui est la plus pratique ou la plus proche des autres connexions est plus soignée et plus facile à utiliser, ou alternativement celle qui est la plus proche de la broche d\'alimentation que tu utilises.<br><br>Par exemple, il est judicieux d\'utiliser la broche physique 17 pour le 3,3 V et la broche physique 25 pour la masse lorsque tu utilises les connexions SPI, car elles sont juste à côté des broches importantes pour le SPI0.', 'black', 300, 340),
            array(15, 15, 'GPIO 22', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>SD0 CLK</td><td>SMI SD14</td><td>DPI D18</td><td>SD1 CLK</td><td>JTAG TRST</td><td></td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 15</li><li>Broche GPIO / BCM : 22</li><li>Broche Wiring Pi : 3</li></ul>', 'green', 150, 380),
            array(16, 16, 'GPIO 23', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>SD0 CMD</td><td>SMI SD15</td><td>DPI D19</td><td>SD1 CMD</td><td>JTAG RTCK</td><td></td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 16</li><li>Broche GPIO / BCM : 23</li><li>Broche Wiring Pi : 4</li></ul>', 'green', 300, 380),
            array(17, 17, '3.3V Power', 'Tous les modèles de Raspberry Pi depuis le B+ peuvent fournir jusqu\'à 500 mA sur les broches 3,3 V, grâce à un régulateur à découpage. Dans certains cas, il peut être possible de tirer plus, mais en raison du manque de documentation et de tests sur les limites réelles, 500 mA est donné comme règle générale.<br><br>La broche d\'alimentation 3,3 V sur les premiers Raspberry Pi avait un courant maximum disponible de seulement 50 mA.<br><br>Il est recommandé d\'utiliser l\'alimentation 5 V couplée à un régulateur 3,3 V pour alimenter les projets en 3,3 V.<br><br>Le blog <a href=\"https://raspberrypise.tumblr.com/post/144555785379/exploring-the-33v-power-rail\" target=\"_blank\" rel=\"noopener\">Piversify</a> en anglais, propose une exploration du rail d\'alimentation 3,3 V sur le Raspberry Pi B+.', 'orange', 150, 420),
            array(18, 18, 'GPIO 24', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>SD0 DAT0</td><td>SMI SD16</td><td>DPI D20</td><td>SD1 DAT0</td><td>JTAG TDO</td><td></td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 18</li><li>Broche GPIO / BCM : 24</li><li>Broche Wiring Pi : 5</li></ul>', 'green', 300, 420),
            array(19, 19, 'GPIO 10 (SPI0 MOSI)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>SPI0 MOSI</td><td>SMI SD2</td><td>DPI D6</td><td>AVEOUT VID6</td><td>AVEIN VID6</td><td></td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 19</li><li>Broche GPIO / BCM : 10</li><li>Broche Wiring Pi : 12</li></ul>', 'purple', 150, 460),
            array(20, 20, 'Masse', 'Les broches de masse sur le Raspberry Pi sont toutes électriquement connectées, donc peu importe laquelle tu utilises si tu câbles une alimentation en tension.<br><br>En général, celle qui est la plus pratique ou la plus proche des autres connexions est plus soignée et plus facile à utiliser, ou alternativement celle qui est la plus proche de la broche d\'alimentation que tu utilises.<br><br>Par exemple, il est judicieux d\'utiliser la broche physique 17 pour le 3,3 V et la broche physique 25 pour la masse lorsque tu utilises les connexions SPI, car elles sont juste à côté des broches importantes pour le SPI0.', 'black', 300, 460),
            array(21, 21, 'GPIO 9 (SPI0 MISO)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>SPI0 MISO</td><td>SMI SD1</td><td>DPI D5</td><td>AVEOUT VID5</td><td>AVEIN VID5</td><td></td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 21</li><li>Broche GPIO / BCM : 9</li><li>Broche Wiring Pi : 13</li></ul>', 'purple', 150, 500),
            array(22, 22, 'GPIO 25', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>SD0 DAT1</td><td>SMI SD17</td><td>DPI D21</td><td>SD1 DAT1</td><td>JTAG TCK</td><td></td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 22</li><li>Broche GPIO / BCM : 25</li><li>Broche Wiring Pi : 6</li></ul>', 'green', 300, 500),
            array(23, 23, 'GPIO 11 (SPI0 SCLK)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>SPI0 SCLK</td><td>SMI SD3</td><td>DPI D7</td><td>AVEOUT VID7</td><td>AVEIN VID7</td><td></td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 23</li><li>Broche GPIO / BCM : 11</li><li>Broche Wiring Pi : 14</li></ul>', 'purple', 150, 540),
            array(24, 24, 'GPIO 8 (Sélection de puce SPI 0)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>SPI0 CE0</td><td>SMI SD0</td><td>DPI D4</td><td>AVEOUT VID4</td><td>AVEIN VID4</td><td></td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 24</li><li>Broche GPIO / BCM : 8</li><li>Broche Wiring Pi : 10</li></ul>', 'purple', 300, 540),
            array(25, 25, 'Masse', 'Les broches de masse sur le Raspberry Pi sont toutes électriquement connectées, donc peu importe laquelle tu utilises si tu câbles une alimentation en tension.<br><br>En général, celle qui est la plus pratique ou la plus proche des autres connexions est plus soignée et plus facile à utiliser, ou alternativement celle qui est la plus proche de la broche d\'alimentation que tu utilises.<br><br>Par exemple, il est judicieux d\'utiliser la broche physique 17 pour le 3,3 V et la broche physique 25 pour la masse lorsque tu utilises les connexions SPI, car elles sont juste à côté des broches importantes pour le SPI0.', 'black', 150, 580),
            array(26, 26, 'GPIO 7 (Sélection de puce SPI 1)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>SPI0 CE1</td><td>SMI SWE_N / SRW_N</td><td>DPI D3</td><td>AVEOUT VID3</td><td>AVEIN VID3</td><td></td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 26</li><li>Broche GPIO / BCM : 7</li><li>Broche Wiring Pi : 11</li></ul>', 'purple', 300, 580),
            array(27, 27, 'GPIO 0 (EEPROM SDA)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>I2C0 SDA</td><td>SMI SA5</td><td>DPI CLK</td><td>AVEOUT VCLK</td><td>AVEIN VCLK</td><td></td></tr></tbody></table><strong>GPIO 0 (Données I2C de l\'EEPROM HAT)</strong><ul><li>Broche physique / Sur la carte : 27</li><li>Broche GPIO / BCM : 0</li><li>Broche Wiring Pi : 30</li></ul>Ces broches sont généralement réservées à la communication I2C avec un EEPROM.<br><br>Connectez ces broches pour la configuration automatique, si la carte offre cette fonctionnalité (consultez la description de la carte pour plus de détails sur la fonctionnalité de l\'EEPROM).', 'blue', 150, 620),
            array(28, 28, 'GPIO 1 (EEPROM SCL)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>I2C0 SCL</td><td>SMI SA4</td><td>DPI DEN</td><td>AVEOUT DSYNC</td><td>AVEIN DSYNC</td><td></td></tr></tbody></table><strong>GPIO 1 (Horloge I2C de l\'EEPROM HAT)</strong><ul><li>Broche physique / Sur la carte : 28</li><li>Broche GPIO / BCM : 1</li><li>Broche Wiring Pi : 31</li></ul>', 'blue', 300, 620),
            array(29, 29, 'GPIO 5', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>GPCLK1</td><td>SMI SA0</td><td>DPI D1</td><td>AVEOUT VID1</td><td>AVEIN VID1</td><td>JTAG TDO</td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 29</li><li>Broche GPIO / BCM : 5</li><li>Broche Wiring Pi : 21</li></ul>', 'green', 150, 660),
            array(30, 30, 'Masse', '<span style="color: #333333;font-family: Poppins, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen-Sans, Ubuntu, Cantarell, \'Helvetica Neue\', sans-serif">Les broches de masse sur le Raspberry Pi sont toutes électriquement connectées, donc peu importe laquelle tu utilises si tu câbles une alimentation en tension. En général, celle qui est la plus pratique ou la plus proche des autres connexions est plus soignée et plus facile à utiliser, ou alternativement celle qui est la plus proche de la broche d\'alimentation que tu utilises. Par exemple, il est judicieux d\'utiliser la broche physique 17 pour le 3,3 V et la broche physique 25 pour la masse lorsque tu utilises les connexions SPI, car elles sont juste à côté des broches importantes pour le SPI0.</span>', 'black', 300, 660),
            array(31, 31, 'GPIO 6', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>GPCLK2</td><td>SMI SOE_N / SE</td><td>DPI D2</td><td>AVEOUT VID2</td><td>AVEIN VID2</td><td>JTAG RTCK</td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 31</li><li>Broche GPIO / BCM : 6</li><li>Broche Wiring Pi : 22</li></ul>', 'green', 150, 700),
            array(32, 32, 'GPIO 12 (PWM0)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>PWM0</td><td>SMI SD4</td><td>DPI D8</td><td>AVEOUT VID8</td><td>AVEIN VID8</td><td>JTAG TMS</td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 32</li><li>Broche GPIO / BCM : 12</li><li>Broche Wiring Pi : 26</li></ul>', 'green', 300, 700),
            array(33, 33, 'GPIO 13 (PWM1)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>PWM1</td><td>SMI SD5</td><td>DPI D9</td><td>AVEOUT VID9</td><td>AVEIN VID9</td><td>JTAG TCK</td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 33</li><li>Broche GPIO / BCM : 13</li><li>Broche Wiring Pi : 23</li></ul>', 'green', 150, 740),
            array(34, 34, 'Masse', 'Les broches de masse sur le Raspberry Pi sont toutes électriquement connectées, donc peu importe laquelle tu utilises si tu câbles une alimentation en tension. En général, celle qui est la plus pratique ou la plus proche des autres connexions est plus soignée et plus facile à utiliser, ou alternativement celle qui est la plus proche de la broche d\'alimentation que tu utilises. Par exemple, il est judicieux d\'utiliser la broche physique 17 pour le 3,3 V et la broche physique 25 pour la masse lorsque tu utilises les connexions SPI, car elles sont juste à côté des broches importantes pour le SPI0.', 'black', 300, 740),
            array(35, 35, 'GPIO 19 (PCM F S)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>PCM FS</td><td>SMI SD11</td><td>DPI D15</td><td>I2CSL SCL / SCLK</td><td>SPI1 MISO</td><td>PWM1</td></tr></tbody></table><strong>GPIO 19 (PCM Frame Sync)</strong><ul><li>Broche physique / Sur la carte : 35</li><li>Broche GPIO / BCM : 19</li><li>Broche Wiring Pi : 24</li></ul>La broche GPIO 19 est utilisée par le PCM pour fournir un signal de synchronisation de trame à un appareil audio externe, tel qu\'une puce DAC.', 'turquoise', 150, 780),
            array(36, 36, 'GPIO 16', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>FL0</td><td>SMI SD8</td><td>DPI D12</td><td>UART0 CTS</td><td>SPI1 CE2</td><td>UART1 CTS</td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 36</li><li>Broche GPIO / BCM : 16</li><li>Broche Wiring Pi : 27</li></ul>', 'green', 300, 780),
            array(37, 37, 'GPIO 26', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>SD0 DAT2</td><td>TE0</td><td>DPI D22</td><td>SD1 DAT2</td><td>JTAG TDI</td><td></td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 37</li><li>Broche GPIO / BCM : 26</li><li>Broche Wiring Pi : 25</li></ul>', 'green', 150, 820),
            array(38, 38, 'GPIO 20 (PCM Data-In)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>PCM DIN</td><td>SMI SD12</td><td>DPI D16</td><td>I2CSL MISO</td><td>SPI1 MOSI</td><td>GPCLK0</td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 38</li><li>Broche GPIO / BCM : 20</li><li>Broche Wiring Pi : 28</li></ul>La broche GPIO 20 est utilisée par le PCM pour recevoir des données d\'un appareil audio I2S, tel qu\'un microphone.', 'turquoise', 300, 820),
            array(39, 39, 'Masse', '<span style="color: #333333;font-family: Poppins, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen-Sans, Ubuntu, Cantarell, \'Helvetica Neue\', sans-serif">Les broches de masse sur le Raspberry Pi sont toutes électriquement connectées, donc peu importe laquelle tu utilises si tu câbles une alimentation en tension. En général, celle qui est la plus pratique ou la plus proche des autres connexions est plus soignée et plus facile à utiliser, ou alternativement celle qui est la plus proche de la broche d\'alimentation que tu utilises. Par exemple, il est judicieux d\'utiliser la broche physique 17 pour le 3,3 V et la broche physique 25 pour la masse lorsque tu utilises les connexions SPI, car elles sont juste à côté des broches importantes pour le SPI0.</span>', 'black', 150, 860),
            array(40, 40, 'GPIO 21 (PCM Data-Out)', '<table><thead><tr><th>Alt0</th><th>Alt1</th><th>Alt2</th><th>Alt3</th><th>Alt4</th><th>Alt5</th></tr></thead><tbody><tr><td>PCM DOUT</td><td>SMI SD13</td><td>DPI D17</td><td>I2CSL CE</td><td>SPI1 SCLK</td><td>GPCLK1</td></tr></tbody></table>&nbsp;<ul><li>Broche physique / Sur la carte : 40</li><li>Broche GPIO / BCM : 21</li><li>Broche Wiring Pi : 29</li></ul>La broche GPIO 21 est utilisée par le PCM pour fournir un signal de sortie de données à un appareil audio externe, tel qu\'une puce DAC.', 'turquoise', 300, 860),
        );

        foreach ($pins as $pin) {
            $wpdb->insert($table_name, array(
                'id' => $pin[0],
                'num_broche' => $pin[1],
                'nom' => $pin[2],
                'description' => $pin[3],
                'couleur' => $pin[4],
                'cx' => $pin[5],
                'cy' => $pin[6],
            ));
        }
    }
}

// Hook pour l'activation du plugin
function pinout_activate_plugin()
{
    pinout_insert_default_pins();
}
register_activation_hook(__FILE__, 'pinout_activate_plugin');


// Fonction AJAX pour récupérer les détails de la broche
// Fonction AJAX pour récupérer les détails de la broche
function get_pin_details()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'pinout_pins';

    // Récupération de l'ID de la broche envoyée par l'AJAX
    $pin_id = intval($_POST['pin_id']);

    // Récupérer les informations de la broche depuis la base de données
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT pin_name, pin_description FROM $table_name WHERE id = %d",
        $pin_id
    ), ARRAY_A); // Changer pin_number en id

    // Vérification si les données existent
    if ($result) {
        // Envoyer les données en format JSON
        wp_send_json_success(array(
            'name' => $result['pin_name'],
            'description' => $result['pin_description']
        ));
    } else {
        // Retourner une erreur si aucune donnée n'a été trouvée
        wp_send_json_error('Détails de la broche non trouvés.');
    }

    wp_die(); // Nécessaire pour arrêter l'exécution d'AJAX
}
add_action('wp_ajax_get_pin_details', 'get_pin_details');
add_action('wp_ajax_nopriv_get_pin_details', 'get_pin_details');




// Fonction pour ajouter le menu d'administration
// Fonction pour ajouter le menu d'administration
function pinout_add_admin_menu()
{
    add_menu_page(
        'Gestion des broches',
        'Pinout',
        'manage_options',
        'pinout_admin',
        'pinout_admin_page',
        'dashicons-admin-tools',
        6
    );

    // Crée la page d'édition des broches
    add_submenu_page(
        null, // Pas de parent direct dans le menu
        'Modifier une broche',
        'Modifier',
        'manage_options',
        'pinout_edit',
        'pinout_handle_edit_page'
    );
}
add_action('admin_menu', 'pinout_add_admin_menu');

// Fonction pour afficher la page d'administration avec le tableau des broches et le formulaire d'ajout/modification
function pinout_admin_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'pinout_pins';

    // Récupération des broches existantes dans la base de données
    $results = $wpdb->get_results("SELECT * FROM $table_name");

    // Si l'utilisateur clique sur "modifier", récupérer les détails de la broche
    $editing_pin = null;
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $editing_pin = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
    }

?>
    <div class="wrap">
        <h1>Gestion des broches</h1>

        <!-- Affichage du tableau des broches -->
        <h2>Liste des broches</h2>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Numéro de la broche</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Coordonnées (CX, CY)</th>
                    <th>Couleur</th> <!-- Ajoute une colonne pour la couleur -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results): ?>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row->id); ?></td>
                            <td><?php echo esc_html($row->pin_number); ?></td>
                            <td><?php echo esc_html($row->pin_name); ?></td>
                            <td><?php echo wp_trim_words($row->pin_description, 10); ?></td>
                            <td>(<?php echo esc_html($row->cx); ?>, <?php echo esc_html($row->cy); ?>)</td>
                            <td style="background-color: <?php echo esc_attr($row->pin_color); ?>;"><?php echo esc_html(ucfirst($row->pin_color)); ?></td> <!-- Affiche la couleur -->
                            <td>
                                <a href="?page=pinout_admin&action=edit&id=<?php echo esc_attr($row->id); ?>">Modifier</a> |
                                <a href="?page=pinout_admin&action=delete&id=<?php echo esc_attr($row->id); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette broche ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Aucune broche n'a été ajoutée pour l'instant.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Formulaire d'ajout ou de modification d'une broche -->
        <h2><?php echo $editing_pin ? 'Modifier la broche' : 'Ajouter une nouvelle broche'; ?></h2>
        <form method="post" action="">
            <input type="hidden" name="pin_id" value="<?php echo $editing_pin ? esc_attr($editing_pin->id) : ''; ?>">

            <label for="pin_number">Numéro de la broche</label>
            <input type="number" name="pin_number" id="pin_number" value="<?php echo $editing_pin ? esc_attr($editing_pin->pin_number) : ''; ?>" required oninput="calculateCoordinates()">

            <label for="pin_name">Nom de la broche</label>
            <input type="text" name="pin_name" id="pin_name" value="<?php echo $editing_pin ? esc_attr($editing_pin->pin_name) : ''; ?>" required>
            <label for="pin_color">Couleur de la broche</label>
            <select name="pin_color" id="pin_color" required>
                <option value="orange" <?php echo isset($editing_pin) && $editing_pin->pin_color === 'orange' ? 'selected' : ''; ?>>Orange</option>
                <option value="black" <?php echo isset($editing_pin) && $editing_pin->pin_color === 'black' ? 'selected' : ''; ?>>Noir</option>
                <option value="blue" <?php echo isset($editing_pin) && $editing_pin->pin_color === 'blue' ? 'selected' : ''; ?>>Bleu</option>
                <option value="purple" <?php echo isset($editing_pin) && $editing_pin->pin_color === 'purple' ? 'selected' : ''; ?>>Violet</option>
                <option value="pink" <?php echo isset($editing_pin) && $editing_pin->pin_color === 'pink' ? 'selected' : ''; ?>>Rose</option>
                <option value="green" <?php echo isset($editing_pin) && $editing_pin->pin_color === 'green' ? 'selected' : ''; ?>>Vert</option>
                <option value="turquoise" <?php echo isset($editing_pin) && $editing_pin->pin_color === 'turquoise' ? 'selected' : ''; ?>>Turquoise</option>
                <option value="red" <?php echo isset($editing_pin) && $editing_pin->pin_color === 'red' ? 'selected' : ''; ?>>Rouge</option>
            </select>
            <br />
            <br />
            <label for="pin_description">Description de la broche</label>
            <?php
            // Utiliser TinyMCE pour la description, pré-remplir si on est en mode "modifier"
            wp_editor($editing_pin ? $editing_pin->pin_description : '', 'pin_description', array(
                'textarea_name' => 'pin_description',
                'media_buttons' => false,
                'teeny' => true,
                'textarea_rows' => 10
            ));
            ?>


            <!-- Champs cachés pour CX et CY calculés automatiquement -->
            <input type="hidden" name="cx" id="cx" value="<?php echo $editing_pin ? esc_attr($editing_pin->cx) : ''; ?>" required>
            <input type="hidden" name="cy" id="cy" value="<?php echo $editing_pin ? esc_attr($editing_pin->cy) : ''; ?>" required>

            <input type="submit" name="<?php echo $editing_pin ? 'update_pin' : 'submit_pin'; ?>" value="<?php echo $editing_pin ? 'Mettre à jour la broche' : 'Ajouter la broche'; ?>">
        </form>
    </div>

    <script type="text/javascript">
        function calculateCoordinates() {
            const pinNumber = document.getElementById('pin_number').value;
            let cx = 150; // Valeur par défaut pour la colonne de gauche (impair)
            let cy = 100 + (Math.floor((pinNumber - 1) / 2) * 40); // Calcul vertical

            if (pinNumber % 2 === 0) {
                // Si la broche est paire, colonne de droite
                cx = 300;
            }

            // Assigner les valeurs calculées aux champs cachés
            document.getElementById('cx').value = cx;
            document.getElementById('cy').value = cy;
        }
    </script>
    <?php
}

// Fonction pour gérer la soumission du formulaire d'ajout ou de modification de broche
function pinout_handle_form_submission()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'pinout_pins';

    if (isset($_POST['submit_pin'])) {
        // Récupérer les données du formulaire pour l'ajout d'une broche
        $pin_number = intval($_POST['pin_number']);
        $pin_name = sanitize_text_field($_POST['pin_name']);
        $pin_description = wp_kses_post(wp_unslash($_POST['pin_description'])); // Utiliser wp_unslash
        $cx = intval($_POST['cx']);
        $cy = intval($_POST['cy']);

        // Insertion dans la base de données
        $wpdb->insert($table_name, array(
            'pin_number' => $pin_number,
            'pin_name' => $pin_name,
            'pin_description' => $pin_description,
            'pin_color' => sanitize_text_field($_POST['pin_color']), // Ajoute cette ligne
            'cx' => $cx,
            'cy' => $cy
        ));

        // Redirection après insertion
        wp_redirect(admin_url('admin.php?page=pinout_admin'));
        exit;
    }

    if (isset($_POST['update_pin'])) {
        // Récupérer les données du formulaire pour la modification d'une broche
        $id = intval($_POST['pin_id']);
        $pin_number = intval($_POST['pin_number']);
        $pin_name = sanitize_text_field($_POST['pin_name']);
        $pin_description = wp_kses_post(wp_unslash($_POST['pin_description'])); // Utiliser wp_unslash
        $cx = intval($_POST['cx']);
        $cy = intval($_POST['cy']);

        // Mise à jour dans la base de données
        $wpdb->update($table_name, array(
            'pin_number' => $pin_number,
            'pin_name' => $pin_name,
            'pin_description' => $pin_description,
            'pin_color' => sanitize_text_field($_POST['pin_color']), // Ajoute cette ligne
            'cx' => $cx,
            'cy' => $cy
        ), array('id' => $id));

        // Redirection après mise à jour
        wp_redirect(admin_url('admin.php?page=pinout_admin'));
        exit;
    }
}
add_action('admin_init', 'pinout_handle_form_submission');










// Fonction pour afficher la page d'édition de broche
function pinout_handle_edit()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'pinout_pins';

    // Vérifier si l'utilisateur est en train d'éditer une broche avec un ID valide
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = intval($_GET['id']);  // Sécuriser l'ID
        $pin = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

        // Si la broche existe, afficher le formulaire d'édition
        if ($pin) {
    ?>
            <div class="wrap">
                <h2>Modifier la broche <?php echo esc_html($pin->pin_name); ?></h2>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="update_pin">
                    <input type="hidden" name="id" value="<?php echo esc_attr($pin->id); ?>">

                    <label for="pin_number">Numéro de la broche</label>
                    <input type="text" name="pin_number" id="pin_number" value="<?php echo esc_attr($pin->pin_number); ?>" required>

                    <label for="pin_name">Nom de la broche</label>
                    <input type="text" name="pin_name" id="pin_name" value="<?php echo esc_attr($pin->pin_name); ?>" required>

                    <label for="pin_color">Couleur de la broche</label>
                    <select name="pin_color" id="pin_color" required>
                        <option value="orange" <?php selected($pin->pin_color, 'orange'); ?>>Orange</option>
                        <option value="black" <?php selected($pin->pin_color, 'black'); ?>>Noir</option>
                        <option value="blue" <?php selected($pin->pin_color, 'blue'); ?>>Bleu</option>
                        <option value="purple" <?php selected($pin->pin_color, 'purple'); ?>>Violet</option>
                        <option value="pink" <?php selected($pin->pin_color, 'pink'); ?>>Violet</option>
                        <option value="green" <?php selected($pin->pin_color, 'green'); ?>>Vert</option>
                        <option value="turquoise" <?php selected($pin->pin_color, 'turquoise'); ?>>Turquoise</option>
                        <option value="red" <?php selected($pin->pin_color, 'red'); ?>>Rouge</option>
                    </select>

                    <label for="pin_description">Description de la broche</label>
                    <textarea name="pin_description" id="pin_description" required><?php echo esc_textarea($pin->pin_description); ?></textarea>

                    <input type="submit" name="update_pin" value="Mettre à jour la broche">
                </form>
            </div>
        <?php
        } else {
            echo "<p>Broche non trouvée.</p>";
        }
    } else {
        echo "<p>ID de broche invalide.</p>";
    }
}

// Gestion de la page d'édition des broches
function pinout_handle_edit_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'pinout_pins';

    // Vérifier que l'utilisateur a bien accès à cette page
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas l\'autorisation d\'accéder à cette page.'));
    }

    // Vérifier si l'utilisateur est en train d'éditer une broche avec un ID valide
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = intval($_GET['id']);  // Sécuriser l'ID
        $pin = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

        // Si la broche existe, afficher le formulaire d'édition
        if ($pin) {
        ?>
            <div class="wrap">
                <h2>Modifier la broche <?php echo esc_html($pin->pin_name); ?></h2>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="update_pin">
                    <input type="hidden" name="id" value="<?php echo esc_attr($pin->id); ?>">

                    <label for="pin_number">Numéro de la broche</label>
                    <input type="text" name="pin_number" id="pin_number" value="<?php echo esc_attr($pin->pin_number); ?>" required>

                    <label for="pin_name">Nom de la broche</label>
                    <input type="text" name="pin_name" id="pin_name" value="<?php echo esc_attr($pin->pin_name); ?>" required>
                    <label for="pin_color">Couleur de la broche</label>
                    <select name="pin_color" id="pin_color" required>
                        <option value="orange" <?php selected($pin->pin_color, 'orange'); ?>>Orange</option>
                        <option value="black" <?php selected($pin->pin_color, 'black'); ?>>Noir</option>
                        <option value="blue" <?php selected($pin->pin_color, 'blue'); ?>>Bleu</option>
                        <option value="purple" <?php selected($pin->pin_color, 'purple'); ?>>Violet</option>
                        <option value="pink" <?php selected($pin->pin_color, 'pink'); ?>>Violet</option>
                        <option value="green" <?php selected($pin->pin_color, 'green'); ?>>Vert</option>
                        <option value="turquoise" <?php selected($pin->pin_color, 'turquoise'); ?>>Turquoise</option>
                        <option value="red" <?php selected($pin->pin_color, 'red'); ?>>Rouge</option>
                    </select>
                    <label for="pin_description">Description de la broche</label>
                    <textarea name="pin_description" id="pin_description" required><?php echo esc_textarea($pin->pin_description); ?></textarea>

                    <input type="submit" name="update_pin" value="Mettre à jour la broche">
                </form>
            </div>
    <?php
        } else {
            echo "<p>Broche non trouvée.</p>";
        }
    } else {
        echo "<p>ID de broche invalide.</p>";
    }
}

// Associer la page d'édition à la fonction via un hook
add_action('admin_menu', 'pinout_add_admin_menu');

// Gestion de la soumission de modification
function pinout_handle_update()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'pinout_pins';

    if (isset($_POST['update_pin'])) {
        $id = intval($_POST['id']);
        $pin_number = intval($_POST['pin_number']);
        $pin_name = sanitize_text_field($_POST['pin_name']);
        $pin_description = sanitize_textarea_field($_POST['pin_description']);

        // Mettre à jour la broche dans la base de données
        $wpdb->update($table_name, array(
            'pin_number' => $pin_number,
            'pin_name' => $pin_name,
            'pin_description' => $pin_description
        ), array('id' => $id));

        // Redirection après la mise à jour, AVANT toute sortie HTML
        wp_redirect(admin_url('admin.php?page=pinout_admin'));
        exit; // Arrêter le script après la redirection
    }
}
add_action('admin_post_update_pin', 'pinout_handle_update');

// Gestion de la suppression d'une broche
function pinout_handle_delete()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'pinout_pins';

    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $wpdb->delete($table_name, array('id' => $id));
        // Redirection après suppression
        wp_redirect(admin_url('admin.php?page=pinout_admin'));
        exit;
    }
}
add_action('admin_init', 'pinout_handle_delete');


// Fonction pour gérer l'ajout d'une nouvelle broche
function pinout_handle_add_pin()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'pinout_pins';

    if (isset($_POST['add_pin'])) {
        // Récupérer les données du formulaire
        $pin_number = intval($_POST['pin_number']);
        $pin_name = sanitize_text_field($_POST['pin_name']);
        $pin_description = sanitize_textarea_field($_POST['pin_description']);

        // Insertion dans la base de données
        $wpdb->insert($table_name, array(
            'pin_number' => $pin_number,
            'pin_name' => $pin_name,
            'pin_description' => $pin_description,
            'pin_color' => sanitize_text_field($_POST['pin_color']), // Ajoute cette ligne
        ));

        // Redirection après insertion
        wp_redirect(admin_url('admin.php?page=pinout_admin&message=added'));
        exit;
    }
}
add_action('admin_post_add_pin', 'pinout_handle_add_pin');


function pinout_display_svg_with_raspberry_shortcode()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'pinout_pins';

    // Récupérer toutes les broches depuis la base de données
    $pins = $wpdb->get_results("SELECT * FROM $table_name");

    ob_start(); // Démarre la mise en tampon de sortie

    // Définir l'espacement vertical entre les broches
    $vertical_spacing = 50; // Espacement vertical entre les broches
    $num_pins = count($pins); // Nombre total de broches
    $marge_basse = 30; // Marge à la fin après la dernière broche

    // Calculer la position de la dernière broche
    $last_pin_cy = 80 + (floor(($num_pins - 1) / 2) * $vertical_spacing);

    // Hauteur totale du rectangle
    $total_height = max($last_pin_cy + $marge_basse + 50, 150); // 50 pour le padding et 150 comme hauteur minimale

    ?>
    <div id="pinout-container" style="display: flex; flex-wrap: wrap; justify-content: space-between; max-width: 100%; padding: 20px; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">

        <!-- Section Raspberry Pi (SVG) à gauche -->
        <div id="svg-container" style="flex: 1 1 300px; max-width: 60%; margin-right: 5px; min-width: 250px;">
            <svg id="rpi-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 <?php echo $total_height; ?>" style="border: none; max-width: 100%;">
                <!-- Le rectangle principal pour représenter le Raspberry Pi (plaque verte) avec coins arrondis -->
                <rect x="50" y="50" width="440" height="<?php echo $total_height - 100; ?>" fill="#4CAF50" rx="20" ry="20" /> <!-- Ajuster la hauteur -->

                <!-- Génération des broches depuis la base de données -->
                <?php
                $left_column_x = 200;  // Colonne de gauche (impaires)
                $right_column_x = 300; // Colonne de droite (paires)

                // Position de départ pour cy (première broche)
                $start_cy = 80; // Position de départ verticale

                foreach ($pins as $pin) :
                    // Calcul de la position cy (verticale) pour chaque broche
                    $cy = $start_cy + (floor(($pin->pin_number - 1) / 2) * $vertical_spacing);

                    // Déterminer si la broche est paire ou impaire pour calculer cx
                    if ($pin->pin_number % 2 == 0) {
                        // Broche paire (colonne de droite)
                        $cx = $right_column_x;
                    } else {
                        // Broche impaire (colonne de gauche)
                        $cx = $left_column_x;
                    }
                ?>
                    <circle class="pin" data-pin-id="<?php echo esc_attr($pin->id); ?>" cx="<?php echo esc_attr($cx); ?>" cy="<?php echo esc_attr($cy); ?>" r="18" fill="<?php echo esc_attr($pin->pin_color); ?>" />

                    <text x="<?php echo esc_attr($cx); ?>" y="<?php echo esc_attr($cy); ?>" font-size="12" fill="#fff" text-anchor="middle" dominant-baseline="middle">
                        <?php echo esc_html($pin->pin_number); ?>
                    </text>
                    <text x="<?php echo esc_attr($cx + ($pin->pin_number % 2 == 0 ? 30 : -30)); ?>" y="<?php echo esc_attr($cy); ?>" font-size="10" fill="#000" text-anchor="<?php echo esc_attr($pin->pin_number % 2 == 0 ? 'start' : 'end'); ?>" dominant-baseline="middle">
                        <?php echo esc_html($pin->pin_name); ?>
                    </text>
                <?php endforeach; ?>
            </svg>
            <!-- Légende des couleurs -->
            <div id="color-legend" style="margin-top: 20px;">
                <h3 style="font-size:18px;">Légende des couleurs</h3>
                <table style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <td style="background-color: orange; width: 20px; height: 20px;"></td>
                        <td>3.3V Power</td>
                    </tr>
                    <tr>
                        <td style="background-color: red; width: 20px; height: 20px;"></td>
                        <td>5V Power</td>
                    </tr>
                    <tr>
                        <td style="background-color: blue; width: 20px; height: 20px;"></td>
                        <td>I2C (inter-integrated Circuit)</td>
                    </tr>
                    <tr>
                        <td style="background-color: green; width: 20px; height: 20px;"></td>
                        <td>GPIO (General Purpose Input Output</td>
                    </tr>
                    <tr>
                        <td style="background-color: black; width: 20px; height: 20px;"></td>
                        <td>Masse (Ground)</td>
                    </tr>
                    <tr>
                        <td style="background-color: purple; width: 20px; height: 20px;"></td>
                        <td>SPI (Serial Peripheral Interface)</td>
                    </tr>
                    <tr>
                        <td style="background-color: pink; width: 20px; height: 20px;"></td>
                        <td>UART (Universal Asynchronous Receiver/Transmitter)</td>
                    </tr>
                    <tr>
                        <td style="background-color: turquoise; width: 20px; height: 20px;"></td>
                        <td>PCM (Pulse Code Mudulation)</td>
                    </tr>
                    <!-- Ajoute d'autres couleurs si nécessaire -->
                </table>
            </div>
        </div>

        <!-- Section informations sur la broche à droite -->
        <div id="pinout-details" style="flex: 1 1 300px; max-width: 100%; margin-left: 5px; padding: 10px; border-radius: 10px; background-color: #ffffff; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: auto;">
            <h2 style="font-family: 'Arial', sans-serif; color: #333; font-size: 14px;">Détails de la broche</h2>
            <p style="color: #555;">Sélectionnez une broche pour voir ses détails.</p>
            <!-- Détails ici -->
        </div>

    </div>

    <style>
        /* Styles pour les appareils mobiles */
        @media (max-width: 768px) {
            #pinout-container {
                flex-direction: column;
                /* Passe à une colonne sur mobile */
            }

            #svg-container {
                max-width: 100%;
                /* Utilise toute la largeur disponible */
                margin-right: 0;
                /* Supprime la marge à droite */
                min-width: auto;
                /* Permet de s'ajuster automatiquement */
            }

            #pinout-details {
                max-width: 100%;
                /* Utilise toute la largeur disponible */
                margin-left: 0;
                /* Supprime la marge à gauche */
                padding: 15px;
                /* Ajoute un peu plus de padding */
                overflow: auto;
                /* Permet le défilement si le contenu déborde */
            }

            text {
                font-size: 10px;
                /* Augmente la taille de la police sur mobile */
            }

            .pin {
                r: 25;
                /* Augmente la taille des broches */
            }

            table {
                width: 100%;
                /* Utilise toute la largeur de la div */
                border-collapse: collapse;
                /* Supprime l'espace entre les cellules */
            }
        }
    </style>
<?php
    return ob_get_clean(); // Renvoie le contenu mis en tampon
}

add_shortcode('pinout_display_svg_with_raspberry', 'pinout_display_svg_with_raspberry_shortcode');
