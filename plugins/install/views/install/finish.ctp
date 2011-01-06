<div class="install">
    <h2><?php echo $title_for_layout; ?></h2>

    <p>
        Username: admin<br />
        Password: password
    </p>

    <br />
    <br />

    <p>
        Delete the installation directory <strong>/app/plugins/install</strong>.
    </p>

    <br />
    <br />

    <?php
        echo $this->Html->link(__('Click here to delete installation files', true), array(
            'plugin' => 'install',
            'controller' => 'install',
            'action' => 'finish',
            'delete' => 1,
        ));
    ?>
</div>