<?php

namespace frontend\modules\models;

use Yii;

/**
 * This is the model class for table "notification".
 *
 * @property integer $notification_id
 * @property integer $type
 * @property string $target
 * @property integer $from
 * @property integer $to
 * @property integer $is_read
 * @property string $ts
 * @property string $heading
 * @property string $content
 */
class Notification extends \yii\db\ActiveRecord
{

    const NF_NEW_ADMIN = 3;
    const NF_NEW_STEWARD = 2;
    const NF_NEW_USER = 1;

    const NF_TARGET_ADMINS = 'admin';
    const NF_TARGET_STEWARDS = 'steward';
    const NF_TARGET_GROUP = 'group';
    const NF_TARGET_STUDENT = 'student';
    
    const NF_HEADING_USER_ADDED = 'Пользователь добавлен';
    const NF_HEADING_ADMIN_ADDED = 'Администратор добавлен';
    const NF_HEADING_STEWARD_ADDED = 'Староста добавлен';

    
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'target', 'from', 'heading', 'content'], 'required'],
            [['type', 'from', 'to', 'is_read'], 'integer'],
            [['ts'], 'safe'],
            [['target'], 'string', 'max' => 16],
            [['heading'], 'string', 'max' => 40],
            [['content'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'notification_id' => 'Notification ID',
            'type' => 'Type',
            'target' => 'Target',
            'from' => 'From',
            'to' => 'To',
            'is_read' => 'Is Read',
            'ts' => 'Ts',
            'heading' => 'Heading',
            'content' => 'Content',
        ];
    }
}
