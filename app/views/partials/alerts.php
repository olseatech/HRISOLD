<?php if (!empty($success ?? null)): ?>
    <div class="alert alert-success" role="alert">
        <?= e((string) $success) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error ?? null)): ?>
    <div class="alert alert-danger" role="alert">
        <?= e((string) $error) ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors ?? null) && is_array($errors)): ?>
    <div class="alert alert-danger" role="alert" aria-live="polite">
        <?php if (count($errors) === 1): ?>
            <?= e((string) reset($errors)) ?>
        <?php else: ?>
            <strong>Please fix the following errors:</strong>
            <ul class="inline-list">
                <?php foreach ($errors as $message): ?>
                    <li><?= e((string) $message) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php endif; ?>
