<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class LaratrustSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'dashboard.manage', 'display_name' => 'Manage Dashboard', 'description' => 'Access and manage dashboard'],
            ['name' => 'dashboard.stats', 'display_name' => 'View Dashboard Stats', 'description' => 'View dashboard statistics'],
            ['name' => 'dashboard.nps-breakdown', 'display_name' => 'View NPS Breakdown', 'description' => 'View NPS breakdown on dashboard'],
            ['name' => 'dashboard.category-comparison', 'display_name' => 'View Category Comparison', 'description' => 'View category comparison'],
            ['name' => 'dashboard.top-keywords', 'display_name' => 'View Top Keywords', 'description' => 'View top keywords analysis'],
            ['name' => 'dashboard.nps-trend', 'display_name' => 'View NPS Trend', 'description' => 'View NPS trend charts'],
            ['name' => 'dashboard.rating-trend', 'display_name' => 'View Rating Trend', 'description' => 'View rating trend charts'],
            ['name' => 'dashboard.sentiment-trend', 'display_name' => 'View Sentiment Trend', 'description' => 'View sentiment trend analysis'],
            ['name' => 'dashboard.daily-performance', 'display_name' => 'View Daily Performance', 'description' => 'View daily performance metrics'],
            ['name' => 'dashboard.qr-code', 'display_name' => 'Manage QR Code', 'description' => 'Manage QR code on dashboard'],
            ['name' => 'dashboard.review-link', 'display_name' => 'View Review Link', 'description' => 'View and copy review link'],
            ['name' => 'dashboard.public-profile', 'display_name' => 'View Public Profile', 'description' => 'View public profile link'],
            ['name' => 'dashboard.feedback-trend', 'display_name' => 'View Feedback Trend', 'description' => 'View feedback trend charts'],
            ['name' => 'dashboard.sentiment-analysis', 'display_name' => 'View Sentiment Analysis', 'description' => 'View sentiment analysis'],
            ['name' => 'dashboard.rating-distribution', 'display_name' => 'View Rating Distribution', 'description' => 'View rating distribution'],
            ['name' => 'dashboard.recent-feedback', 'display_name' => 'View Recent Feedback', 'description' => 'View recent feedback list'],

            ['name' => 'feedback.manage', 'display_name' => 'Manage Feedback', 'description' => 'Access to feedback management'],
            ['name' => 'feedback.stats', 'display_name' => 'View Feedback Stats', 'description' => 'View feedback statistics'],
            ['name' => 'feedback.reply', 'display_name' => 'Reply to Feedback', 'description' => 'Reply to customer feedback'],
            ['name' => 'feedback.flag', 'display_name' => 'Flag Feedback', 'description' => 'Flag inappropriate feedback'],
            ['name' => 'feedback.unflag', 'display_name' => 'Unflag Feedback', 'description' => 'Unflag feedback'],
            ['name' => 'feedback.sentiment', 'display_name' => 'View Sentiment', 'description' => 'View feedback sentiment analysis'],

            ['name' => 'customer.manage', 'display_name' => 'Manage Customers', 'description' => 'Access to customer management'],
            ['name' => 'customer.stats', 'display_name' => 'View Customer Stats', 'description' => 'View customer statistics'],
            ['name' => 'customer.create', 'display_name' => 'Create Customer', 'description' => 'Create new customers'],
            ['name' => 'customer.view', 'display_name' => 'View Customer', 'description' => 'View customer details'],
            ['name' => 'customer.delete', 'display_name' => 'Delete Customer', 'description' => 'Delete customers'],
            ['name' => 'customer.edit', 'display_name' => 'Edit Customer', 'description' => 'Edit customer details'],
            ['name' => 'customer.import', 'display_name' => 'Import Customers', 'description' => 'Import customers from CSV'],
            ['name' => 'customer.export', 'display_name' => 'Export Customers', 'description' => 'Export customers to CSV'],

            ['name' => 'review-request.manage', 'display_name' => 'Manage Review Requests', 'description' => 'Access to review requests'],
            ['name' => 'review-request.stats', 'display_name' => 'View Review Request Stats', 'description' => 'View review request statistics'],
            ['name' => 'review-request.create', 'display_name' => 'Create Review Request', 'description' => 'Create new review requests'],
            ['name' => 'review-request.view', 'display_name' => 'View Review Request', 'description' => 'View review request details'],
            ['name' => 'review-request.send', 'display_name' => 'Send Review Request', 'description' => 'Send review requests to customers'],
            ['name' => 'review-request.delete', 'display_name' => 'Delete Review Request', 'description' => 'Delete review requests'],
            ['name' => 'review-request.edit', 'display_name' => 'Edit Review Request', 'description' => 'Edit review requests'],

            ['name' => 'qr.manage', 'display_name' => 'Manage QR Codes', 'description' => 'Access to QR code management'],
            ['name' => 'qr.create', 'display_name' => 'Create QR Code', 'description' => 'Generate QR codes'],
            ['name' => 'qr.poster-create', 'display_name' => 'Create QR Poster', 'description' => 'Create QR code posters'],

            ['name' => 'business-settings.manage', 'display_name' => 'Manage Settings', 'description' => 'Access to business settings'],
            ['name' => 'business-settings.feedback', 'display_name' => 'Manage Feedback Settings', 'description' => 'Manage feedback settings'],
            ['name' => 'business-settings.display', 'display_name' => 'Manage Display Settings', 'description' => 'Manage display settings'],
            ['name' => 'business-settings.notifications', 'display_name' => 'Manage Notifications', 'description' => 'Manage notification settings'],
            ['name' => 'business-settings.moderation', 'display_name' => 'Manage Moderation', 'description' => 'Manage moderation settings'],

            ['name' => 'api-integration.manage', 'display_name' => 'Manage API Integration', 'description' => 'Access to API integration'],
            ['name' => 'api-integration.stats', 'display_name' => 'View API Stats', 'description' => 'View API integration statistics'],
            ['name' => 'api-integration.create-key', 'display_name' => 'Create API Key', 'description' => 'Create new API keys'],
            ['name' => 'api-integration.delete-key', 'display_name' => 'Delete API Key', 'description' => 'Delete API keys'],
            ['name' =>'api-integration.revoke-key', 'display_name' => 'Revoke API Key', 'description' => 'Revoke API keys'],
            ['name' => 'api-integration.update-key', 'display_name' => 'Update API Key', 'description' => 'Update API key details'],

            ['name' => 'team.user-manage', 'display_name' => 'Manage Team Users', 'description' => 'Access to team user management'],
            ['name' => 'team.user-create', 'display_name' => 'Create Team User', 'description' => 'Invite new team members'],
            ['name' => 'team.user-edit', 'display_name' => 'Edit Team User', 'description' => 'Edit team member details'],
            ['name' => 'team.user-delete', 'display_name' => 'Delete Team User', 'description' => 'Remove team members'],

            ['name' => 'team.role-manage', 'display_name' => 'Manage Roles', 'description' => 'Access to role management'],
            ['name' => 'team.role-create', 'display_name' => 'Create Role', 'description' => 'Create new roles'],
            ['name' => 'team.role-edit', 'display_name' => 'Edit Role', 'description' => 'Edit existing roles'],
            ['name' => 'team.role-delete', 'display_name' => 'Delete Role', 'description' => 'Delete roles'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                [
                    'display_name' => $permissionData['display_name'],
                    'description' => $permissionData['description'],
                ]
            );
        }

        $ownerRole = Role::firstOrCreate(
            ['name' => 'owner'],
            [
                'display_name' => 'Business Owner',
                'description' => 'Access to all business features',
            ]
        );

        $ownerRole->syncPermissions(Permission::all());

        if ($this->command) {
            $this->command->info('Roles and permissions created successfully!');
        }
    }


    public static function syncOwnerPermissions(Business $business): int
    {
        $ownerRole = Role::where('name', 'owner')->first();
        
        if (!$ownerRole) {
            return 0;
        }

        $ownerRole->syncPermissions(Permission::all());

        $owners = $business->users()->wherePivot('role', 'owner')->get();
        $syncedCount = 0;

        foreach ($owners as $owner) {
            if (!$owner->hasRole('owner', $business)) {
                $owner->addRole($ownerRole, $business);
                $syncedCount++;
            }
        }

        return $syncedCount;
    }
}
