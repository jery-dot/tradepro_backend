<?php

namespace App\Models;

use App\Enums\UserType;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\HasMany; 

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'latitude',
        'longitude',
        'city',
        'state',
        'country',
        'job_requirements',
        'rating',
        'status',
        'available_today',
        'profile_image',
        'fcm_token',
        // Stripe columns
        'stripe_id',
        'stripe_status',
        'default_payment_method',
        'trial_ends_at',
        'active_subscription_id'

    ];

    protected $hidden = [
        'password',
    ];

    public function getLocationTextAttribute(): string
    {
        $location = '';
        if ($this->city) {
            $location .= $this->city;
        }
        if ($this->state) {
            if (! empty($location)) {
                $location .= ', ';
            }
        }
        if ($this->country) {
            if (! empty($location)) {
                $location .= ', ';
            }
            $location .= $this->country;
        }

        return $location;
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'user_type' => UserType::class,
            'job_requirements' => 'array',
        ];
    }
/**
 * Récupère dynamiquement le niveau d'expérience depuis le modèle lié.
 */
public function getExperienceDisplayAttribute(): string
{
    // Si c'est un ouvrier, on cherche dans la table laborer
    if ($this->user_type === \App\Enums\UserType::LABORER) {
        return $this->laborer?->experience_level ?? '—';
    }

    // Si c'est un apprenti, on cherche dans la table apprentice
    if ($this->user_type === \App\Enums\UserType::APPRENTICE) {
        return $this->apprentice?->experience_level ?? '—';
    }

    // Pour les Contractors / Subcontractors
    return '—';
}
/**
 * Analyse et centralise l'état de l'assurance pour tous les types de profils.
 */
public function getInsuranceStatusAttribute(): array
{
    // 1. CONTRACTOR
    if ($this->user_type === \App\Enums\UserType::CONTRACTOR) {
        $path = $this->contractor?->file_path; // Ajustez 'file_path' si la colonne s'appelle autrement
        return [
            'status' => $path ? 'Document' : 'No',
            'url'    => $path ? asset('storage/' . $path) : null
        ];
    }

    // 2. SUBCONTRACTOR
    if ($this->user_type === \App\Enums\UserType::SUBCONTRACTOR) {
        $path = $this->subcontractor?->insurance_file_path;
        return [
            'status' => $path ? 'Document' : 'No',
            'url'    => $path ? asset('storage/' . $path) : null
        ];
    }

    // 3. LABORER
    if ($this->user_type === \App\Enums\UserType::LABORER) {
        $hasInsurance = (bool) ($this->laborer?->has_insurance ?? false);
        return ['status' => $hasInsurance ? 'Yes' : 'No', 'url' => null];
    }

    // 4. APPRENTICE
    if ($this->user_type === \App\Enums\UserType::APPRENTICE) {
        $hasInsurance = (bool) ($this->apprentice?->has_insurance ?? false);
        return ['status' => $hasInsurance ? 'Yes' : 'No', 'url' => null];
    }

    return ['status' => '—', 'url' => null];
}
    // JWT methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Profiles

    public function laborer(): HasOne
    {
        return $this->hasOne(Laborer::class);
    }

    public function subcontractor(): HasOne
    {
        return $this->hasOne(Subcontractor::class);
    }

    public function contractor(): HasOne
    {
        return $this->hasOne(Contractor::class);
    }

    public function apprentice(): HasOne
    {
        return $this->hasOne(Apprentice::class);
    }

    /**
     * Récupère toutes les offres d'emploi postées par cet utilisateur.
     */
    public function jobPosts(): HasMany
    {
        // 🔥 On lie l'User aux JobPosts via la clé étrangère (ex: 'owner_id' ou 'user_id')
        // Ajustez 'owner_id' selon le nom réel de votre colonne dans la table job_posts
        return $this->hasMany(JobPost::class, 'user_id'); 
    }

    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function reviews(){
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Role label from user_type enum
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->user_type) {
            UserType::CONTRACTOR => 'Contractor',
            UserType::SUBCONTRACTOR => 'Subcontractor',
            UserType::LABORER => 'Laborer',
            UserType::APPRENTICE => 'Apprentice',
            default => 'Unknown'
        };
    }

    /**
     * Ratings data (avg + count)
     */
    public function getRatingsDataAttribute()
    {
        $rating = round($this->receivedReviews()->avg('overall_rating') ?? 0, 1);
        $count = $this->receivedReviews()->count();

        return [
            'rating' => $rating,
            'ratings_count' => $count,
        ];
    }

    /**
     * Uploaded document metadata (null-safe)
     */
    public function getUploadedDocumentAttribute()
    {
        if (! $this->profile_document_url) {
            return null;
        }

        return [
            'file_name' => $this->profile_document_name,
            'file_size' => $this->profile_document_size,
            'document_url' => $this->profile_document_url,
        ];
    }

    public function opportunities(){
        return $this->hasMany(Opportunity::class);
    }

    // public function activeSubscription()
    // {
    //     return $this->belongsTo(UserSubscription::class, 'active_subscription_id');
    // }
   public function activeSubscription()
    {
        return $this->belongsTo(UserSubscription::class, 'active_subscription_id');
    }


    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function plan()
    {
        return $this->hasOneThrough(Plan::class, UserSubscription::class);
    }

    public function subscriptionPlan(): ?Plan
    {
        return $this->activeSubscription?->plan;
    } 
    /**
     * Check if the user has an active, paid subscription or is on trial.
     */
    public function isSubscribed(): bool
    {
        return $this->activeSubscription?->stripe_status === 'active';
    }

    /**
     * Get the apprentice profile associated with the user.
     */
    public function apprenticeProfile()
    {
        // Adjust 'hasOne' to 'belongsTo' depending on your database schema
        return $this->hasOne(ApprenticeProfile::class); 
    }
}
