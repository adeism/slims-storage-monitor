<?php
/**
 * SLiMS Storage Monitor - Common Functions
 * Author: Ade Ismail Siregar (adeismailbox@gmail.com) 19-May-2025 13:36
 */
defined('INDEX_AUTH') or die('Direct access not allowed!');

// Add .php, .env, and .sh to ignored files by default
$IGNORED_FILES = ['index.php', 'index.html', 'index.htm', '.htaccess', '.php', '.env', '.sh'];
$KEEP_HTML_IN  = ['files/reports']; // Folders where HTML files are okay and shouldn't be ignored by default

// Default folder map - Paths are relative to SLiMS root via constants like SB, UPLOAD, IMGBS
$DEFAULT_FOLDER_MAP = [
  'repository'    => defined('REPOBS') ? rtrim(REPOBS, DS) : SB.'repository',
  'files/backup'  => UPLOAD.'backup', // e.g., <SLiMS_ROOT>/files/backup/
  'files/reports' => UPLOAD.REP.DS,   // e.g., <SLiMS_ROOT>/files/reports/
  'images/docs'   => IMGBS.'docs',    // e.g., <SLiMS_ROOT>/images/docs/
  'images/persons'=> IMGBS.'persons',
  'images/barcodes'=>IMGBS.'barcodes',
  'images/content'=> IMGBS.'content',
  'plugins'       => SB.'plugins',    // e.g., <SLiMS_ROOT>/plugins/
];

global $sysconf;
// Allow overriding or adding paths via SLiMS system configuration
$FOLDER_MAP = $DEFAULT_FOLDER_MAP;
if (isset($sysconf['folder_size_report_paths']) && is_array($sysconf['folder_size_report_paths'])) {
  $FOLDER_MAP = array_merge($DEFAULT_FOLDER_MAP, $sysconf['folder_size_report_paths']);
}

function dirSize(string $path): int {
  $bytes = 0;
  if (!is_dir($path) || !is_readable($path)) return 0;
  try {
    $iterator = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS)
    );
    foreach ($iterator as $file) {
      if ($file->isFile() && $file->isReadable()) {
        $bytes += $file->getSize();
      }
    }
  } catch (UnexpectedValueException $e) {
    error_log("SLiMS Storage Monitor: Could not access path during dirSize: " . $e->getMessage());
    return 0;
  }
  return $bytes;
}

function scanStats(string $label, string $path, array $IGNORED_FILES, array $KEEP_HTML_IN): array {
  if (!is_dir($path) || !is_readable($path)) {
    return [0, 0, ['N/A' => __('Path not accessible or not a directory')], []];
  }

  if ($label==='plugins') {
    $cnt=0;$bytes=0;$top=[]; $exts=[];
    $glob_path = rtrim($path, DS) . DS . '*';
    foreach (glob($glob_path, GLOB_ONLYDIR) as $d) {
      if (!is_readable($d)) continue;
      $cnt++; $sz = dirSize($d); $bytes += $sz; $top[$d]=$sz;
    }
    arsort($top);
    $slice = array_slice($top,0,5,true);
    $formattedTop = [];
    foreach ($slice as $dir => $sz) {
        $formattedTop[] = ['name' => basename($dir), 'bytes' => $sz, 'type' => 'folder'];
    }
    if ($cnt > 0) $exts = [__('Folders') => $cnt]; else $exts = [__('No plugins found') => 0];
    return [$cnt,$bytes,$exts, $formattedTop];
  }

  $cnt=0;$bytes=0;$exts=[];$top=[];
  try {
    $it = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($path,FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS)
    );
    foreach ($it as $f) {
      if (!$f->isReadable()) continue;
      
      $name = $f->getFilename();
      $lower_name = strtolower($name); 
      $file_ext_lower = strtolower(pathinfo($name,PATHINFO_EXTENSION)); 

      $is_ignored_by_name = in_array($lower_name, array_map('strtolower', $IGNORED_FILES), true);
      $is_ignored_by_ext = in_array('.' . $file_ext_lower, array_map('strtolower', $IGNORED_FILES), true);

      if (!in_array($label,$KEEP_HTML_IN,true) && ($is_ignored_by_name || $is_ignored_by_ext) && $f->isFile()) {
        continue;
      }

      $sz = $f->getSize();
      $bytes+=$sz;

      if ($f->isFile()) {
          $cnt++;
          $ext = empty($file_ext_lower) ? 'noext' : $file_ext_lower;
          $exts[$ext] = ($exts[$ext]??0)+1;

          $relPath = str_replace(rtrim($path, DS) . DS, '', $f->getPathname());
          if (count($top)<5) {
            $top[]=['name'=>$relPath,'bytes'=>$sz, 'type' => 'file'];
            usort($top,fn($a,$b)=>$a['bytes']<=>$b['bytes']);
          } elseif ($sz>$top[0]['bytes']) {
            array_shift($top);
            $top[]=['name'=>$relPath,'bytes'=>$sz, 'type' => 'file'];
            usort($top,fn($a,$b)=>$a['bytes']<=>$b['bytes']);
          }
      }
    }
  } catch (UnexpectedValueException $e) {
     error_log("SLiMS Storage Monitor: Could not access path during scanStats for label '{$label}': " . $e->getMessage());
     return [$cnt,$bytes,['N/A' => __('Error scanning directory')], $top];
  }
  arsort($exts);
  usort($top,fn($a,$b)=>$b['bytes']<=>$a['bytes']);
  return [$cnt,$bytes,$exts,$top];
}

function humanSize(int $bytes): string {
  if ($bytes <=0) return '0 B';
  $units = ['B', 'KB', 'MB', 'GB', 'TB'];
  $i = floor(log($bytes, 1024));
  $i = min($i, count($units) - 1);
  return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i];
}
?>