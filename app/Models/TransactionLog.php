<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionLog extends Model
{
    protected $fillable = [
        'user_id',
        'discovery_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'metadata',
        'performed_by_type',
        'performed_by_identifier',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    // Action constants
    const ACTION_CREATED = 'created';
    const ACTION_STATUS_CHANGED = 'status_changed';
    const ACTION_APPROVED = 'approved';
    const ACTION_REJECTED = 'rejected';
    const ACTION_ASSIGNED = 'assigned';
    const ACTION_UNASSIGNED = 'unassigned';
    const ACTION_UPDATED = 'updated';
    const ACTION_DELETED = 'deleted';
    const ACTION_VIEWED = 'viewed';
    const ACTION_SHARED = 'shared';
    const ACTION_ACTIVATED = 'activated';
    const ACTION_DEACTIVATED = 'deactivated';
    const ACTION_PRICE_CHANGED = 'price_changed';
    const ACTION_QUANTITY_CHANGED = 'quantity_changed';
    const ACTION_ATTACHED = 'attached';
    const ACTION_DETACHED = 'detached';
    const ACTION_PROMOTED = 'promoted';
    const ACTION_DEMOTED = 'demoted';
    const ACTION_TRANSFERRED = 'transferred';

    // Entity type constants
    const ENTITY_DISCOVERY = 'discovery';
    const ENTITY_ITEM = 'item';
    const ENTITY_PROPERTY = 'property';
    const ENTITY_USER = 'user';
    const ENTITY_COMPANY = 'company';
    const ENTITY_WORKGROUP = 'workgroup';

    // Performer type constants
    const PERFORMER_USER = 'user';
    const PERFORMER_CUSTOMER = 'customer';
    const PERFORMER_SYSTEM = 'system';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function discovery(): BelongsTo
    {
        return $this->belongsTo(Discovery::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'entity_id')->where('entity_type', self::ENTITY_ITEM);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'entity_id')->where('entity_type', self::ENTITY_PROPERTY);
    }

    public function workgroup(): BelongsTo
    {
        return $this->belongsTo(WorkGroup::class, 'entity_id')->where('entity_type', self::ENTITY_WORKGROUP);
    }

    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entity_id')->where('entity_type', self::ENTITY_USER);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'entity_id')->where('entity_type', self::ENTITY_COMPANY);
    }

    public function getRelatedEntity()
    {
        switch ($this->entity_type) {
            case self::ENTITY_DISCOVERY:
                return $this->discovery;
            case self::ENTITY_ITEM:
                return $this->item;
            case self::ENTITY_PROPERTY:
                return $this->property;
            case self::ENTITY_WORKGROUP:
                return $this->workgroup;
            case self::ENTITY_USER:
                return $this->relatedUser;
            case self::ENTITY_COMPANY:
                return $this->company;
            default:
                return null;
        }
    }

    public function getActionTextAttribute(): string
    {
        $actionTexts = [
            self::ACTION_CREATED => 'Oluşturuldu',
            self::ACTION_STATUS_CHANGED => 'Durum değiştirildi',
            self::ACTION_APPROVED => 'Müşteri tarafından onaylandı',
            self::ACTION_REJECTED => 'Müşteri tarafından reddedildi',
            self::ACTION_ASSIGNED => 'Atandı',
            self::ACTION_UNASSIGNED => 'Atama kaldırıldı',
            self::ACTION_UPDATED => 'Güncellendi',
            self::ACTION_DELETED => 'Silindi',
            self::ACTION_VIEWED => 'Görüntülendi',
            self::ACTION_SHARED => 'Paylaşıldı',
            self::ACTION_ACTIVATED => 'Aktifleştirildi',
            self::ACTION_DEACTIVATED => 'Deaktifleştirildi',
            self::ACTION_PRICE_CHANGED => 'Fiyat değiştirildi',
            self::ACTION_QUANTITY_CHANGED => 'Miktar değiştirildi',
            self::ACTION_ATTACHED => 'Bağlandı',
            self::ACTION_DETACHED => 'Bağlantısı kesildi',
            self::ACTION_PROMOTED => 'Yetki yükseltildi',
            self::ACTION_DEMOTED => 'Yetki düşürüldü',
            self::ACTION_TRANSFERRED => 'Transfer edildi',
        ];

        return $actionTexts[$this->action] ?? $this->action;
    }

    public function getEntityTypeTextAttribute(): string
    {
        $entityTexts = [
            self::ENTITY_DISCOVERY => 'Keşif',
            self::ENTITY_ITEM => 'Malzeme',
            self::ENTITY_PROPERTY => 'Mülk',
            self::ENTITY_USER => 'Kullanıcı',
            self::ENTITY_COMPANY => 'Şirket',
            self::ENTITY_WORKGROUP => 'Çalışma Grubu',
        ];

        return $entityTexts[$this->entity_type] ?? $this->entity_type;
    }

    public function getPerformerNameAttribute(): string
    {
        if ($this->performed_by_type === self::PERFORMER_USER && $this->user) {
            return $this->user->name;
        } elseif ($this->performed_by_type === self::PERFORMER_CUSTOMER) {
            return $this->performed_by_identifier ?? 'Müşteri';
        } elseif ($this->performed_by_type === self::PERFORMER_SYSTEM) {
            return 'Sistem';
        }

        return 'Bilinmeyen';
    }
}
