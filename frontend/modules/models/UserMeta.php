<?php

namespace frontend\modules\models;

use Yii;

/**
 * This is the model class for table "user_meta".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $meta_key
 * @property string $meta_value
 */
class UserMeta extends \yii\db\ActiveRecord
{
	const META_EMPLOYMENT = ['courses', 'sports', 'days'];
	const META_OUT_EMPLOYMENT = ['out_courses', 'out_sports', 'out_days', 'charge'];
	const META_PARENTS = ['p_name', 'p_phone', 'p_address', 'p_employment'];
	const META_BREEDING = ['intelligence', 'mercy', 'justice', 'responsibility', 'character', 'avg'];

	const META_HEALTH = ['health_group', 'insurance_policy', 'health_recs'];

	const TEACHER_META_TYPES = [
		10 => 'metaPersonal'
	];
	const META_TYPES = [
		'metaEmployments',
		'metaOutEmployments',
		'metaHealths',
		'metaParents',
		'metaBreeding'
	];
	const META_PERSONAL = ['experience', 'category', 'attestation_period', 'college_theme', 'teacher_theme', 'info'];


	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_meta';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'meta_key'], 'required'],
            [['user_id'], 'integer'],
            [['meta_key'], 'string', 'max' => 50],
            [['meta_value'], 'string', 'max' => 1500],
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
            'meta_key' => 'Meta Key',
            'meta_value' => 'Meta Value',
        ];
    }


}
