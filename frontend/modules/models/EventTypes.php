<?php

namespace frontend\modules\models;


/**
 * This is the model class for table "event_types".
 *
 * @property integer $id
 * @property string $name
 * @property string $color
 */
class EventTypes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'event_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'color'], 'required'],
            [['name'], 'string', 'max' => 40],
            [['color'], 'string', 'max' => 7],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'color' => 'Color',
        ];
    }
}
