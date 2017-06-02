<?php

namespace frontend\modules\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "attachments".
 *
 * @property integer $id
 * @property string $title
 * @property string $thumbnail
 * @property string $source
 * @property integer $user_id
 * @property integer $type
 */
class Attachment extends \yii\db\ActiveRecord
{

	const TYPE_PHOTO = 100;
	const TYPE_DOC = 101;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'attachments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'thumbnail', 'source', 'user_id', 'type'], 'required'],
            [['user_id', 'type'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['thumbnail', 'source'], 'string', 'max' => 300],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'thumbnail' => 'Thumbnail',
            'source' => 'Source',
            'user_id' => 'User ID',
        ];
    }

	/**
	 * @param string $path
	 * @param string $ext
	 *
	 * @return string
	 */
    private function createThumbnail($path, $ext) {

	    @list($width, $height) = getimagesize($path);
	    $sw = $width;
	    $sh = $height;

	    if ($width < 250 && $height < 480) return $path;

    	$createImage = 'imagecreatefromjpg';
    	$saveImage = 'imagejpg';

    	switch ($ext) {
		    case 'png': {
		    	$createImage = 'imagecreatefrompng';
			    $saveImage = 'imagepng';
			    break;
		    }

		    case 'jpeg': {
			    $createImage = 'imagecreatefromjpeg';
			    $saveImage = 'imagejpeg';
			    break;
		    }

		    case 'bmp': {
			    $createImage = 'imagecreatefrombmp';
			    $saveImage = 'imagebmp';
			    break;
		    }
	    }

	    if ($width > 250) {
    		$k = 250/$width;
    		$width = 250;
    		$height = $k * $height;
	    }

	    if ($height > 480) {
		    $k = 480/$height;
		    $height= 480;
		    $width= $k * $width;
	    }



	    $source = call_user_func($createImage, $path);
    	$thumb = imagecreatetruecolor($width, $height);

    	$resampled = imagecopyresampled($thumb, $source, 0, 0, 0, 0, $width, $height, $sw, $sh);
    	if ($resampled) {
    		$filename = 'uploads/thumb_' . sha1($path) . '.' . $ext;

    		call_user_func($saveImage, $thumb, $filename);
    		return $filename;
	    }

	    return false;

    }

    public function save( $runValidation = true, $attributeNames = null ) {

    	$file = UploadedFile::getInstanceByName('attachment');
    	if ($file) {

    		$filename = 'uploads/' . sha1($file->name) . '.' . $file->extension;
    		$file->saveAs($filename);

    		$this->source = $filename;

    		$filename = $this->createThumbnail($filename, $file->extension);

			if ($filename) {
				$this->thumbnail = $filename;
			}

		    return parent::save( $runValidation, $attributeNames );
	    }

	    return false;

    }
}
