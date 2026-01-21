<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-30
 * Time: 15:28
 */

namespace Sulde\Service;


use Zend\Filter\File\Rename;

class ImageUpload
{

    private $fileName;
    private $fileData;
    private $filePath;
    private $uploadPath;
    private $fileNameNew;

    public function __construct($p_fileName,$p_data,$p_path)
    {
        $this->fileName = $p_fileName;
        $this->fileData = $p_data;
        $this->filePath = $p_path;
        $this->uploadPath = IMAGE_PATH .$p_path;
    }

    private function isUpload(){
        if($this->fileData[$this->fileName]) return true;
        else return false;
    }

    private function getNewFileName(){
        $extension = @pathinfo($this->fileData[$this->fileName]['name'])['extension'];
        $fileName = @pathinfo($this->fileData[$this->fileName]['name'])['filename'];
        $dirname = @pathinfo($this->fileData[$this->fileName]['name'])['dirname'];
//        $time =time();
        if(!$this->fileNameNew){
            $time=str_replace('.','_',microtime(true));
            $this->fileNameNew = $time .$dirname.$extension;
            //return $newFileName;
        }
        return $this->fileNameNew;
    }

    private function getNewFilePath(){
        if(!is_dir($this->uploadPath)){
            mkdir($this->uploadPath,0777, true);
//            chmod($this->uploadPath, 0777);
        }
        return $this->uploadPath . $this->getNewFileName();
    }

    private function getUrlFilePath(){
        return $this->filePath . $this->getNewFileName();
    }

    public function upload(){

        $fileUrl=null;

        if($this->isUpload()) {

            $fileTmpPath = $_FILES[$this->fileName]['tmp_name'];
            $fileName = basename($_FILES[$this->fileName]['name']);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExts = ['jpg', 'jpeg', 'png'];
            if (in_array($fileExt, $allowedExts)) {
                $newFileName = uniqid('img_', true) . '.webp'; // Đổi đuôi sang WebP
                $destination = $this->uploadPath. $newFileName;

                list($width, $height) = getimagesize($fileTmpPath);
                $maxWidth = 800;
                if ($width > $maxWidth) {
                    $newWidth = $maxWidth;
                    $newHeight = intval($height * $newWidth / $width);
                } else {
                    $newWidth = $width;
                    $newHeight = $height;
                }

                // Tạo ảnh từ file upload
                switch ($fileExt) {
                    case 'jpg':
                    case 'jpeg':
                        $sourceImage = imagecreatefromjpeg($fileTmpPath);
                        break;
                    case 'png':
                        $sourceImage = imagecreatefrompng($fileTmpPath);
                        break;
                    default:
                        echo "Định dạng không được hỗ trợ.";
                        exit;
                }

                // Resize ảnh
                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0,
                    $newWidth, $newHeight, $width, $height);

                // Lưu ảnh WebP với chất lượng 80%
                if (imagewebp($resizedImage, $destination, 80)) {
//                    echo "Ảnh đã được upload và chuyển sang WebP: <a href='$destination'>$newFileName</a>";
                    return $this->filePath.$newFileName;
                } else {
//                    echo "Lỗi khi lưu ảnh WebP.";
                    return null;
                }

                imagedestroy($sourceImage);
                imagedestroy($resizedImage);
            } else {
//                echo "Chỉ cho phép file JPG, JPEG, PNG.";
                return null;
            }

            /*$fileFilter = new Rename([
                'target'=>$this->getNewFilePath(),
                'randomize' => false
            ]);
            $fileFilter->filter($this->fileData[$this->fileName]);
            @chmod($this->getNewFilePath(), 0777);


            if(file_exists($this->getNewFilePath()))
                $fileUrl = $this->getUrlFilePath();

            return $fileUrl;*/
        }else
            return null;
    }
}