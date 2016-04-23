<?php
require('./createGif.php');
require_once "./random.php";
function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}

$target_dir = "./tmp/incoming/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
//echo $target_file;
//echo $_FILES["fileToUpload"]["tmp_name"];
$message = $_POST[intensified];
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) 
{
    $imageProps = @getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    //echo "imageProps 1: " . $imageProps;
    if($imageProps !== false) 
    {
        //echo "File is an image - " . $imageProps["mime"] . ".";
        $uploadOk = 1;
    } 
    else 
    {
        echo "File is not an image.";
        $uploadOk = 0;
    }
}
$imageFileType = strtolower($imageFileType);

if(($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") || $uploadOk == 0) 
{
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
} 
else 
{
    //echo "temp file: " . $_FILES["fileToUpload"]["tmp_name"];
    if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) 
    {
        $imageProps = getimagesize($target_file);
        //echo "Image size " . $imageProps[0] . "x" . $imageProps[1];
        //echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
        if($imageProps[0] > 6000 || $imageProps[1] > 6000)
        {
            echo "Sorry, image size cannot exceed 6000x6000.";
            echo "yours is " . $imageProps[0] . "x" . $imageProps[1];
        }
        else
        {
            if($imageFileType == "jpg" || $imageFileType == "jpeg")
            {
                $im = imagecreatefromjpeg($target_file);
            }
            else
            {
                if($imageFileType == "png")
                {
                    $im = imagecreatefrompng($target_file);
                }
                else
                {
                    $im = imagecreatefromgif($target_file);
                }
            }
            $bbox = imagettfbbox ( 36, 0, "./OpenSans-Bold.ttf", $message);
            //echo $bbox[0] . " " . $bbox[1] . " " . $bbox[2] . " " . $bbox[3] . " " . $bbox[4] . " " . $bbox[5] . " " . $bbox[6] . " " . $bbox[7];
            $xsize = abs($bbox[0]) + abs($bbox[2])  + 50;
            $ysize = abs($bbox[5]) + abs($bbox[1]);
            $aspectRatio = $imageProps[0] / $imageProps[1];
        // Width of result is a bit bigger than the space need for the text. Calculate the height to conserve original aspect ratio
            $newHeight = $xsize / $aspectRatio;
            $dst_img = ImageCreateTrueColor($xsize, $newHeight);
            imagegif($im,"./orig.gif");
            //echo $imageProps[0],$imageProps[1],$newHeight;
            $dst_img = imagescale($im, $xsize, $newHeight);
            imagegif($dst_img, "./dst.gif"); 
            $crop1 = ImageCreateTrueColor($xsize - 20, $newHeight);
            imagecopy($crop1, $dst_img, 0, 0, 0, 0, $xsize - 20, $newHeight);
            $white = imagecolorallocate($crop1, 0xFF, 0xFF, 0xFF);
            imagettftext($crop1, 36, 0, abs($bbox[0]), $newHeight / 2, $white, "./OpenSans-Bold.ttf", $message);
            imagegif($crop1,"./crop1.gif");
            $crop2 = ImageCreateTrueColor($xsize - 20, $newHeight);
            imagecopy($crop2, $dst_img, 0, 0, 20, 0, $xsize - 20, $newHeight);
            $white2 = imagecolorallocate($crop2, 0xFF, 0xFF, 0xFF);
            imagettftext($crop2, 36, 0, abs($bbox[0]), $newHeight / 2, $white2, "./OpenSans-Bold.ttf", $message);
            imagegif($crop2,"./crop2.gif");
            $frames = array($crop1, $crop2);
            $durations = array(10, 10);
            $anim = new GifCreator\AnimGif();
            $anim->create($frames, $durations);
            $name = "result" . random_str(10) . ".gif";
            $fullName = "http://www.wegmuller.org/intensifier/" . $name;
            $anim->save("../intensifier/" . $name);
            $result = $anim->get();
//            header('Content-Type: image/gif');
//            echo $result;
//            exit;
        }
    }
    else
    {
        echo "Sorry, there was an error uploading your file. Here's the last successful image instead...";
    }

}
?>
<p><img src="<?=$fullName?>">



