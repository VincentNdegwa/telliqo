<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/password/confirm';
import { Form, Head } from '@inertiajs/vue3';
import { Lock, Shield, ArrowRight } from 'lucide-vue-next';
</script>

<template>
    <AuthLayout
        title="Confirm your password"
        description="This is a secure area. Please confirm your password to continue."
    >
        <Head title="Confirm password" />

        <!-- Security Notice -->
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/20">
            <div class="flex items-start gap-3">
                <Shield class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
                <div class="space-y-1">
                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                        Security verification required
                    </p>
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        For your security, please verify your identity before accessing this area.
                    </p>
                </div>
            </div>
        </div>

        <Form
            v-bind="store.form()"
            reset-on-success
            v-slot="{ errors, processing }"
            class="space-y-6"
        >
            <div class="space-y-2">
                <Label htmlFor="password" class="text-sm font-medium">Password</Label>
                <div class="relative">
                    <Lock class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        autofocus
                        placeholder="••••••••"
                        class="pl-10"
                    />
                </div>
                <InputError :message="errors.password" />
            </div>

            <Button
                type="submit"
                class="w-full gap-2"
                size="lg"
                :disabled="processing"
                data-test="confirm-password-button"
            >
                <Spinner v-if="processing" />
                <template v-else>
                    Confirm
                    <ArrowRight class="h-4 w-4" />
                </template>
            </Button>
        </Form>
    </AuthLayout>
</template>
