<?php
/**
 * NexGear editorial pager template.
 *
 * Replaces CodeIgniter's stock Bootstrap "default_full" pager (plain blue
 * numbers) with a squared, uppercase Space-Grotesk treatment that matches the
 * storefront's .filter-chip design language. Used by the /collection listing
 * (both the server-rendered page and the AJAX filter response).
 *
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>
<nav class="nexgear-pager" aria-label="Collection pages">
    <a class="nexgear-pager-edge<?= $pager->hasPreviousPage() ? '' : ' is-disabled' ?>"
       <?php if ($pager->hasPreviousPage()): ?>href="<?= esc((string) $pager->getPreviousPage(), 'attr') ?>"<?php else: ?>aria-disabled="true" tabindex="-1"<?php endif ?>
       rel="prev" aria-label="Previous page">
        <span class="nexgear-pager-arrow" aria-hidden="true">&larr;</span>
        <span class="nexgear-pager-edge-label">Prev</span>
    </a>

    <ol class="nexgear-pager-list">
        <?php foreach ($pager->links() as $link): ?>
            <li class="nexgear-pager-item">
                <a class="nexgear-pager-link<?= $link['active'] ? ' is-active' : '' ?>"
                   href="<?= esc((string) $link['uri'], 'attr') ?>"
                   <?= $link['active'] ? 'aria-current="page"' : '' ?>>
                    <?= esc((string) $link['title']) ?>
                </a>
            </li>
        <?php endforeach ?>
    </ol>

    <a class="nexgear-pager-edge<?= $pager->hasNextPage() ? '' : ' is-disabled' ?>"
       <?php if ($pager->hasNextPage()): ?>href="<?= esc((string) $pager->getNextPage(), 'attr') ?>"<?php else: ?>aria-disabled="true" tabindex="-1"<?php endif ?>
       rel="next" aria-label="Next page">
        <span class="nexgear-pager-edge-label">Next</span>
        <span class="nexgear-pager-arrow" aria-hidden="true">&rarr;</span>
    </a>
</nav>
