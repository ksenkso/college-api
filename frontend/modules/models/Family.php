<?php

namespace frontend\modules\models;

use Yii;

/**
 * This is the model class for table "family".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $p_name
 * @property string $p_employment
 * @property string $p_address
 * @property string $p_phone
 * @property string $trouble
 * @property string $consist
 * @property string $edu_type
 * @property string $purposes
 * @property integer $type
 */
class Family extends \yii\db\ActiveRecord
{

	const FAMILY_NORMAL = 1;
	const FAMILY_PROBLEM = 2;
	const FAMILY_POOR = 3;
	const FAMILY_RICH = 4;
	const FAMILY_GUARDED = 5;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'family';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'p_name', 'p_phone'], 'required'],
            [['user_id', 'type'], 'integer'],
            [['p_name'], 'string', 'max' => 255],
            [['p_employment', 'p_address', 'trouble', 'consist', 'edu_type', 'purposes'], 'string', 'max' => 500],
            [['p_phone'], 'string', 'max' => 80],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'p_name' => 'P Name',
            'p_employment' => 'P Employment',
            'p_address' => 'P Address',
            'p_phone' => 'P Phone',
            'trouble' => 'Trouble',
            'consist' => 'Consist',
            'edu_type' => 'Edu Type',
            'purposes' => 'Purposes',
            'type' => 'Type',
        ];
    }

	/**
	 * @param $type
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public static function findByType( $type ) {
		return static::find()
			->with('student')
             ->where(['type' => $type]);
    }

	public function getStudent() {
		return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
