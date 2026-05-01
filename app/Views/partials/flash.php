<?php if (session('success')): ?>
    <div class="alert alert-success vp-alert"><?= esc(session('success')) ?></div>
<?php endif; ?>
<?php if (session('error')): ?>
    <div class="alert alert-danger vp-alert"><?= esc(session('error')) ?></div>
<?php endif; ?>
<?php if (session('errors')): ?>
    <div class="alert alert-danger vp-alert">
        <?php foreach ((array) session('errors') as $error): ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
