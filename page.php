<?php
/**
 * SLiMS Storage Monitor - Main Page
 * Author: Ade Ismail Siregar (adeismailbox@gmail.com) 19-May-2025 13:36
 */
defined('INDEX_AUTH') or die('Direct access not allowed!');

// Ensure SLiMS environment is loaded
if (!defined('SB')) {
    // This path assumes page.php is in SLIMS_ROOT/plugins/slims-storage-monitor/
    // So, sysconfig.inc.php is two levels up from __DIR__
    $sysconfig_path = realpath(__DIR__ . '/../../sysconfig.inc.php');
    if (file_exists($sysconfig_path)) {
        require $sysconfig_path;
    } else {
        die('Failed to load SLiMS sysconfig.inc.php. Path tried: ' . $sysconfig_path);
    }
}

// Start SLiMS session if not already started (usually handled by plugin_container)
if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
    require SB . 'admin/default/session.inc.php';
}

// Check admin session (usually handled by plugin_container)
if (!isset($_SESSION['uid']) && basename($_SERVER['PHP_SELF']) !== 'plugin_container.php') {
    if (basename($_SERVER['PHP_SELF']) === 'page.php') { 
         require SB . 'admin/default/session_check.inc.php';
    }
}


// Include helper functions and configuration
if (!function_exists('dirSize')) { 
    include __DIR__.'/inc/common.inc.php';
}
// Load SLiMS pagination library
require_once SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';


if (!utility::havePrivilege('reporting', 'r')) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

function httpQuery($query = []) {
    unset($query['export_csv']); 
    $current_params = $_GET;
    if (!isset($query['mod']) && isset($current_params['mod'])) $query['mod'] = $current_params['mod'];
    if (!isset($query['id']) && isset($current_params['id'])) $query['id'] = $current_params['id'];
    
    unset($current_params['page']); 
    
    return http_build_query(array_merge($current_params, $query));
}
?>

<div class="container-fluid my-3">

  <!-- Info + Refresh -->
  <div class="alert alert-info d-flex justify-content-between align-items-center fs-6 mb-4">
    <div>
      <i class="fa fa-info-circle"></i>
      <?php if (isset($_GET['detail_folder_label'])): ?>
          <?= __('Details for folder:') ?> <strong><?= htmlentities(urldecode($_GET['detail_folder_label'])) ?></strong>.
          Calculated at <?= date('d M Y H:i:s'); ?>.
          <a href="<?= $_SERVER['PHP_SELF'].'?'.httpQuery(['detail_folder_label' => null, 'detail_folder_path' => null, 'page' => null]) ?>" class="btn btn-secondary btn-sm ms-2"><i class="fa fa-arrow-left"></i> <?=__('Back to Summary')?></a>
      <?php else: ?>
          <?= __('Report calculated at') ?> <?= date('d M Y H:i:s'); ?>
      <?php endif; ?>
    </div>
    <div>
      <a href="<?= $_SERVER['PHP_SELF'].'?'.httpQuery() ?>"
         class="btn btn-primary btn-sm">
        <i class="fa fa-sync-alt"></i> <?= __('Refresh') ?>
      </a>
    </div>
  </div>

  <?php
  // --- SERVER DISK SPACE ESTIMATION ---
  if (!isset($_GET['detail_folder_label'])) { // Only show on summary page
    $disk_total_space_bytes = @disk_total_space(SB); // SLiMS root path
    $disk_free_space_bytes = @disk_free_space(SB);

    if ($disk_total_space_bytes !== false && $disk_free_space_bytes !== false) {
        $disk_used_space_bytes = $disk_total_space_bytes - $disk_free_space_bytes;
        $disk_used_percentage = ($disk_total_space_bytes > 0) ? round(($disk_used_space_bytes / $disk_total_space_bytes) * 100, 2) : 0;

        $bar_color_class = 'bg-success'; // Green
        if ($disk_used_percentage > 90) {
            $bar_color_class = 'bg-danger'; // Red
        } elseif ($disk_used_percentage > 75) {
            $bar_color_class = 'bg-warning text-dark'; // Yellow (text-dark for better contrast)
        }
  ?>
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
      <i class="fa fa-hdd"></i> <strong><?=__('Server Disk Space Estimation (SLiMS Partition)')?></strong>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-4">
          <p class="mb-1"><strong><?=__('Total Space:')?></strong> <?= humanSize($disk_total_space_bytes) ?></p>
          <p class="mb-1"><strong><?=__('Used Space:')?></strong> <?= humanSize($disk_used_space_bytes) ?></p>
          <p class="mb-0"><strong><?=__('Free Space:')?></strong> <?= humanSize($disk_free_space_bytes) ?></p>
        </div>
        <div class="col-md-8 align-self-center">
          <div class="progress" style="height: 25px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated <?= $bar_color_class ?>" role="progressbar" style="width: <?= $disk_used_percentage ?>%;" aria-valuenow="<?= $disk_used_percentage ?>" aria-valuemin="0" aria-valuemax="100">
              <?= $disk_used_percentage ?>% <?=__('Used')?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php
    } else {
  ?>
  <div class="alert alert-warning mb-4">
    <i class="fa fa-exclamation-triangle"></i> <?= __('Could not retrieve server disk space information. This feature might be disabled on your server or PHP does not have enough permissions.') ?>
  </div>
  <?php
    }
  } // End of if (!isset($_GET['detail_folder_label']))
  // --- END SERVER DISK SPACE ESTIMATION ---
  ?>

<?php
if (isset($_GET['detail_folder_label']) && isset($_GET['detail_folder_path'])) {
    // Detail View
    $detail_label = urldecode($_GET['detail_folder_label']);
    $detail_path = urldecode($_GET['detail_folder_path']);

    if (array_key_exists($detail_label, $FOLDER_MAP) && realpath($FOLDER_MAP[$detail_label]) === realpath($detail_path) && is_dir($detail_path) && is_readable($detail_path)) {
        
        $all_items_in_folder = [];
        
        if ($detail_label === 'plugins') { 
            $glob_path = rtrim($detail_path, DS) . DS . '*';
            $all_dirs = glob($glob_path, GLOB_ONLYDIR);
            foreach ($all_dirs as $dir) {
                if (is_readable($dir)) { 
                    $all_items_in_folder[] = ['name' => basename($dir), 'bytes' => dirSize($dir), 'type' => 'folder', 'path' => $dir];
                }
            }
        } else { 
            try {
                $dir_iterator = new RecursiveDirectoryIterator($detail_path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS);
                $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST); 
                foreach ($iterator as $fileinfo) {
                     if ($fileinfo->isReadable()) { 
                        $entryType = $fileinfo->isFile() ? 'file' : ($fileinfo->isDir() ? 'folder' : 'other');
                        if ($entryType === 'other') continue; 

                        $all_items_in_folder[] = [
                            'name' => str_replace(rtrim($detail_path, DS) . DS, '', $fileinfo->getPathname()), 
                            'bytes' => $entryType === 'file' ? $fileinfo->getSize() : dirSize($fileinfo->getPathname()), 
                            'type' => $entryType,
                            'path' => $fileinfo->getPathname() 
                        ];
                    }
                }
            } catch (UnexpectedValueException $e) {
                echo '<div class="alert alert-danger">'.__('Error accessing folder details:').' '.htmlentities($e->getMessage()).'</div>';
            }
        }

        usort($all_items_in_folder, fn($a, $b) => $b['bytes'] <=> $a['bytes']);

        $total_items = count($all_items_in_folder);
        $perPage = 20; 
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $perPage;
        
        $items_to_display = array_slice($all_items_in_folder, $offset, $perPage);

        if (!empty($items_to_display)) {
            echo '<h4>'.sprintf(__('Showing %s - %s of %s Items in %s by Size'), ($offset + 1), ($offset + count($items_to_display)), $total_items, '<strong>'.htmlentities($detail_label).'</strong>').'</h4>';
            
            $base_detail_url = $_SERVER['PHP_SELF'].'?'.httpQuery(['page' => null]); 
            
            if ($total_items > $perPage) {
                echo '<div class="biblioPaging pt-2 pb-2">';
                echo simbio_paging::paging($total_items, $perPage, 10, '', '_self', $base_detail_url.'&page='); 
                echo '</div>';
            }

            echo '<div class="table-responsive mb-4">';
            echo '<table class="table table-hover table-sm table-bordered align-middle">';
            echo '<thead class="table-light"><tr><th>'.__('Name').'</th><th class="text-end">'.__('Size').'</th><th>'.__('Type').'</th></tr></thead><tbody>';
            foreach ($items_to_display as $item) {
                $item_icon = $item['type'] === 'folder' ? 'fa-folder' : 'fa-file';
                echo '<tr>';
                echo '<td class="text-truncate" title="'.htmlentities($item['path']).'"><i class="fa '.$item_icon.'"></i> '.htmlentities($item['name']).'</td>';
                echo '<td class="text-end">'.humanSize($item['bytes']).'</td>';
                echo '<td>'.ucfirst($item['type']).'</td>';
                echo '</tr>';
            }
            echo '</tbody></table></div>';

            if ($total_items > $perPage) {
                echo '<div class="biblioPaging pt-2 pb-2">';
                echo simbio_paging::paging($total_items, $perPage, 10, '', '_self', $base_detail_url.'&page=');
                echo '</div>';
            }

        } else {
            echo '<div class="alert alert-warning">'.__('No items found in this folder or folder is not accessible.').'</div>';
        }

    } else {
        echo '<div class="alert alert-danger">'.__('Invalid folder specified, path mismatch, or folder not readable.').'</div>';
    }

} else { // Summary View
?>
  <!-- Summary table -->
  <div class="table-responsive mb-4">
    <table class="table table-hover table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th><?= __('Folder') ?></th>
          <th class="text-end"><?= __('Items Count') ?></th>
          <th class="text-end"><?= __('Total Size') ?></th>
          <th><?= __('Top File Types (Count)') ?></th>
        </tr>
      </thead>
      <tbody>
      <?php
        $totalFilesCount = 0;
        $totalDiskUsageBytes = 0;
        $cardsHtml = [];

        foreach ($FOLDER_MAP as $label => $path) {
          list($count,$bytes,$extensions,$topFiles) = scanStats($label, $path, $IGNORED_FILES, $KEEP_HTML_IN);
          $totalFilesCount += $count;
          $totalDiskUsageBytes += $bytes;
          $sizeHumanReadable = humanSize($bytes);

          $typesStringArray = [];
          $extLimit = 0;
          foreach ($extensions as $ext => $num) {
            if ($ext === 'N/A') { 
                $typesStringArray[] = $num; 
                break;
            }
            $typesStringArray[] = htmlentities($ext) . ' ('. $num .')';
            $extLimit++;
            if ($extLimit >= 6) break; 
          }
          $typesString = implode(', ', $typesStringArray) . (count($extensions) > 6 && $ext !== 'N/A' ? ', …' : '');

          $detail_link = $_SERVER['PHP_SELF'].'?'.httpQuery(['detail_folder_label' => urlencode($label), 'detail_folder_path' => urlencode($path), 'page' => null]);

          echo "<tr>";
          // Removed the <br><small> part that displayed the path
          echo "<td><a href='{$detail_link}' class='text-primary fw-bold text-decoration-none'>" . htmlentities($label) . "</a></td>";
          echo "<td class='text-end'>{$count}</td>";
          echo "<td class='text-end'>{$sizeHumanReadable}</td>";
          echo "<td>{$typesString}</td>";
          echo "</tr>";

          ob_start();
          ?>
          <div class="card h-100 shadow-sm">
            <div class="card-header py-2 bg-light border-bottom">
              <strong>
                <i class="fa <?= $label === 'plugins' ? 'fa-plug' : 'fa-folder-open' ?>"></i>
                <?= $label === 'plugins'
                     ? __('Top 5 largest plugins')
                     : __('Top 5 largest files in') . ' ' . htmlentities($label) ?>
              </strong>
            </div>
            <div class="card-body p-2">
              <?php if (empty($topFiles)): ?>
                <p class="text-muted mb-0 fst-italic"><?= __('No files/folders found or path not accessible.') ?></p>
              <?php else: ?>
                <table class="table table-sm table-borderless mb-0 small">
                  <?php foreach ($topFiles as $file): ?>
                    <tr>
                      <td class="text-truncate" style="max-width:230px" title="<?= htmlentities($file['name']) ?> (<?= $file['type'] ?>)">
                        <i class="fa <?= $file['type'] === 'folder' ? 'fa-folder' : 'fa-file' ?>"></i>
                        <?= htmlentities($file['name']) ?>
                      </td>
                      <td class="text-end"><?= humanSize($file['bytes']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </table>
              <?php endif; ?>
            </div>
          </div>
          <?php
          $cardsHtml[] = ob_get_clean();
        }
      ?>
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <th><?= __('TOTAL SLiMS Data (Monitored Paths)') ?></th>
          <th class="text-end"><?= number_format($totalFilesCount) ?></th>
          <th class="text-end"><?= humanSize($totalDiskUsageBytes) ?></th>
          <th></th>
        </tr>
      </tfoot>
    </table>
  </div>

  <!-- Cards grid for Top 5 Files/Plugins -->
  <h4 class="my-3"><?= __('Quick View: Largest Items/Plugins') ?></h4>
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
    <?php foreach ($cardsHtml as $cardHtml): ?>
      <div class="col"><?= $cardHtml ?></div>
    <?php endforeach; ?>
  </div>
<?php } // End else for summary view ?>
</div>