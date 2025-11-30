<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { ArrowRight, Lock, Mail } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Welcome back"
        description="Enter your credentials to access your account"
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="rounded-lg bg-green-50 p-3 text-center text-sm font-medium text-green-600 dark:bg-green-950/20"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="space-y-6"
        >
            <div class="space-y-4">
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
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            placeholder="you@example.com"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="errors.email" />
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-sm font-medium"
                            >Password</Label
                        >
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-xs"
                            :tabindex="5"
                        >
                            Forgot password?
                        </TextLink>
                    </div>
                    <div class="relative">
                        <Lock
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center">
                    <Label
                        for="remember"
                        class="flex items-center space-x-2 text-sm font-normal"
                    >
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span class="text-muted-foreground"
                            >Keep me logged in</span
                        >
                    </Label>
                </div>
            </div>

            <Button
                type="submit"
                class="w-full gap-2"
                size="lg"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                <template v-else>
                    Sign in
                    <ArrowRight class="h-4 w-4" />
                </template>
            </Button>

            <div
                class="text-center text-sm text-muted-foreground"
                v-if="canRegister"
            >
                Don't have an account?
                <TextLink :href="register()" :tabindex="5" class="font-medium"
                    >Create account</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
