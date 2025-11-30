<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { email } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { ArrowLeft, Mail } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Forgot your password?"
        description="No worries! Enter your email and we'll send you reset instructions."
    >
        <Head title="Forgot password" />

        <div
            v-if="status"
            class="rounded-lg bg-green-50 p-3 text-center text-sm font-medium text-green-600 dark:bg-green-950/20"
        >
            {{ status }}
        </div>

        <div class="space-y-6">
            <Form
                v-bind="email.form()"
                v-slot="{ errors, processing }"
                class="space-y-4"
            >
                <div class="space-y-2">
                    <Label for="email" class="text-sm font-medium"
                        >Email address</Label
                    >
                    <div class="relative">
                        <Mail
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autocomplete="email"
                            autofocus
                            placeholder="you@example.com"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="errors.email" />
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    size="lg"
                    :disabled="processing"
                    data-test="email-password-reset-link-button"
                >
                    <Spinner v-if="processing" />
                    Send reset link
                </Button>
            </Form>

            <div class="text-center">
                <TextLink
                    :href="login()"
                    class="inline-flex items-center gap-2 text-sm font-medium"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to login
                </TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
