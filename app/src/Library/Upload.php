<?php

namespace App\Library;
/** @SuppressWarnings(PHPMD.StaticAccess) */
class Upload extends Library
{
    public function upload_image($tmpfile, $subdir = NULL, $mime_allowed = array(
        'jpg' => 'image/jpeg',
        'png' => 'image/png',)
    ) 
    {
        $this->dir = $this->public_dir;
        $dir = $this->dir;
        $path = '';
        $curdir = $dir;
        if (!file_exists($curdir) && !is_dir($curdir)) {
            mkdir($curdir);
        }
        if ($subdir !== NULL) {
            $subdirs = explode("/", $subdir);
        foreach ($subdirs as $sub) {
            $curdir = $curdir . '/' . $sub;
                if (!file_exists($curdir) && !is_dir($curdir)) {
             mkdir($curdir);
        }
      }
      $dir = $dir . $subdir;
      $path = $path . $subdir;
    }

    if (false === $ext = array_search(
        $this->get_mime_type($tmpfile), $mime_allowed, true
        )) {
      return false;
    }

    $dest_name = sha1_file($tmpfile);

    if (!move_uploaded_file(
            $tmpfile, sprintf('%s/%s.%s', $dir, $dest_name, $ext
            )
        )) {
      return false;
    }

    return $path . '/' . $dest_name . '.' . $ext;
  }

  private function get_mime_type($filepath) {
    // Check only existing files
    if (!file_exists($filepath) || !is_readable($filepath))
      return false;

    // Trying finfo
    if (function_exists('finfo_open')) {
      $finfo = finfo_open(FILEINFO_MIME);
      $mimeType = finfo_file($finfo, $filepath);
      finfo_close($finfo);
      // Mimetype can come in text/plain; charset=us-ascii form
      if (strpos($mimeType, ';'))
        list($mimeType, ) = explode(';', $mimeType);
      return $mimeType;
    }

    // Trying mime_content_type
    if (function_exists('mime_content_type')) {
      return mime_content_type($filepath);
    }

    // Trying exec
    if (function_exists('system')) {
      $mimeType = system("file -i -b $filepath");
      if (!empty($mimeType))
        return $mimeType;
    }

    // Trying to get mimetype from images
    $imageData = @getimagesize($filepath);
    if (!empty($imageData['mime'])) {
      return $imageData['mime'];
    }

    return false;
  }

}
