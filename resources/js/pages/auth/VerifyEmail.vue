<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import { Form, Head } from '@inertiajs/vue3';
import { CheckCircle2, LogOut, Mail, RefreshCw } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Verify your email"
        description="We've sent a verification link to your email address. Please check your inbox and click the link to continue."
    >
        <Head title="Email verification" />

        <div class="space-y-6">
            <!-- Success Message -->
            <div
                v-if="status === 'verification-link-sent'"
                class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-950/20"
            >
                <div class="flex items-start gap-3">
                    <CheckCircle2
                        class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600"
                    />
                    <div class="space-y-1">
                        <p
                            class="text-sm font-medium text-green-900 dark:text-green-100"
                        >
                            Verification email sent!
                        </p>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            A new verification link has been sent to your email
                            address.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Email Icon -->
            <div class="flex justify-center">
                <div
                    class="flex h-20 w-20 items-center justify-center rounded-full bg-primary/10"
                >
                    <Mail class="h-10 w-10 text-primary" />
                </div>
            </div>

            <!-- Instructions -->
            <div class="space-y-2 text-center">
                <p class="text-sm text-muted-foreground">
                    Didn't receive the email? Check your spam folder or request
                    a new one.
                </p>
            </div>

            <!-- Form -->
            <Form
                v-bind="send.form()"
                class="space-y-4"
                v-slot="{ processing }"
            >
                <Button
                    type="submit"
                    class="w-full gap-2"
                    size="lg"
                    variant="secondary"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    <template v-else>
                        <RefreshCw class="h-4 w-4" />
                        Resend verification email
                    </template>
                </Button>

                <div class="text-center">
                    <TextLink
                        :href="logout()"
                        as="button"
                        class="inline-flex items-center gap-2 text-sm font-medium"
                    >
                        <LogOut class="h-4 w-4" />
                        Log out
                    </TextLink>
                </div>
            </Form>
        </div>
    </AuthLayout>
</template>
