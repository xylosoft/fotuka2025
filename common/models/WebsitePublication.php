<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $template_id
 * @property int $folder_id
 * @property int $user_id
 * @property int|null $customer_id
 * @property string|null $page_title
 * @property string $uri
 * @property string $template_snapshot_json
 * @property string|null $values_json
 * @property int $is_password_protected
 * @property string|null $password_hash
 * @property int $allow_download_all
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $deleted
 * @property int|null $deleted_by_user_id
 */
class WebsitePublication extends ActiveRecord
{
    public $plain_password;

    public static function tableName()
    {
        return 'website_publication';
    }

    public function rules()
    {
        return [
            [['folder_id', 'user_id', 'template_snapshot_json'], 'required'],
            [['template_id', 'folder_id', 'user_id', 'customer_id', 'created_at', 'updated_at', 'deleted', 'deleted_by_user_id'], 'integer'],
            [['page_title'], 'string', 'max' => 255],
            [['uri'], 'string', 'max' => 255],
            [['template_snapshot_json', 'values_json'], 'string'],
            [['is_password_protected', 'allow_download_all'], 'boolean'],
            [['password_hash', 'plain_password'], 'string', 'max' => 255],
            ['uri', 'match', 'pattern' => '/^[A-Za-z0-9\-_]+$/', 'message' => 'Use only letters, numbers, dashes, and underscores.'],
            ['template_id', 'required', 'message' => 'Please select a Template'],
            ['page_title', 'required', 'message' => 'Please enter a Page Title'],
            ['uri', 'required', 'message' => 'Please select a Public URI for your page'],
            ['uri', 'validateActiveUri'],
            ['folder_id', 'validateActiveFolder'],
            ['template_snapshot_json', 'validateSnapshotJson'],
            ['values_json', 'validateValuesJson'],
        ];
    }

    public function validateSnapshotJson($attribute)
    {
        $decoded = json_decode((string) $this->$attribute, true);

        if (!is_array($decoded) || !isset($decoded['page']) || !isset($decoded['components'])) {
            $this->addError($attribute, 'Template snapshot JSON is invalid.');
        }
    }

    public function validateValuesJson($attribute)
    {
        if ($this->$attribute === null || $this->$attribute === '') {
            return;
        }

        $decoded = json_decode((string) $this->$attribute, true);

        if (!is_array($decoded)) {
            $this->addError($attribute, 'Publication values JSON is invalid.');
        }
    }

    public function validateActiveUri($attribute)
    {
        if (!$this->uri) {
            return;
        }

        $query = static::find()
            ->andWhere(['uri' => $this->uri, 'deleted' => null]);

        if (!$this->isNewRecord) {
            $query->andWhere(['<>', 'id', $this->id]);
        }

        if ($query->exists()) {
            $this->addError($attribute, 'That public URI is already in use.');
        }
    }

    public function validateActiveFolder($attribute)
    {
        if (!$this->folder_id) {
            return;
        }

        $query = static::find()
            ->andWhere(['folder_id' => $this->folder_id, 'deleted' => null]);

        if (!$this->isNewRecord) {
            $query->andWhere(['<>', 'id', $this->id]);
        }

        if ($query->exists()) {
            $this->addError($attribute, 'That folder already has a published page.');
        }
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $now = time();

        if ($insert) {
            $this->created_at = $now;
        }

        $this->updated_at = $now;

        if ((int) $this->is_password_protected === 1 && !empty($this->plain_password)) {
            $this->password_hash = Yii::$app->security->generatePasswordHash($this->plain_password);
        }

        if ((int) $this->is_password_protected !== 1) {
            $this->password_hash = null;
        }

        return true;
    }

    public function getTemplate()
    {
        return $this->hasOne(WebsiteTemplate::class, ['id' => 'template_id']);
    }

    public function getSnapshotArray()
    {
        $decoded = json_decode((string) $this->template_snapshot_json, true);

        if (!is_array($decoded)) {
            return WebsiteTemplate::defaultDefinition();
        }

        if (!isset($decoded['page']) || !isset($decoded['components'])) {
            return WebsiteTemplate::defaultDefinition();
        }

        return $decoded;
    }

    public function getValuesArray()
    {
        $decoded = json_decode((string) $this->values_json, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function setSnapshotArray(array $snapshot)
    {
        $this->template_snapshot_json = json_encode($snapshot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function setValuesArray(array $values)
    {
        $this->values_json = json_encode($values, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function validatePassword($plainTextPassword)
    {
        if (!$this->password_hash) {
            return false;
        }

        return Yii::$app->security->validatePassword((string) $plainTextPassword, $this->password_hash);
    }

    public static function findActive($alias = null)
    {
        $query = static::find();

        if ($alias) {
            $query->alias($alias);
            return $query->andWhere([$alias . '.deleted' => null]);
        }

        return $query->andWhere([static::tableName() . '.deleted' => null]);
    }

    public function getFolder()
    {
        return $this->hasOne(Folder::class, ['id' => 'folder_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    public function getWebsiteTemplate()
    {
        return $this->hasOne(WebsiteTemplate::class, ['id' => 'customer_id']);
    }

}