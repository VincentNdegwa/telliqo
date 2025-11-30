<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/vue3';
import { ArrowRight, Lock, Mail, User } from 'lucide-vue-next';
</script>

<template>
    <AuthBase
        title="Create your account"
        description="Start your 14-day free trial. No credit card required."
    >
        <Head title="Register" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="space-y-6"
        >
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="name" class="text-sm font-medium"
                        >Full name</Label
                    >
                    <div class="relative">
                        <User
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            id="name"
                            type="text"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="name"
                            name="name"
                            placeholder="John Doe"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="errors.name" />
                </div>

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
                            required
                            :tabindex="2"
                            autocomplete="email"
                            name="email"
                            placeholder="you@example.com"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="errors.email" />
                </div>

                <div class="space-y-2">
                    <Label for="password" class="text-sm font-medium"
                        >Password</Label
                    >
                    <div class="relative">
                        <Lock
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            id="password"
                            type="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            name="password"
                            placeholder="••••••••"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <div class="space-y-2">
                    <Label
                        for="password_confirmation"
                        class="text-sm font-medium"
                        >Confirm password</Label
                    >
                    <div class="relative">
                        <Lock
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            id="password_confirmation"
                            type="password"
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="••••••••"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="errors.password_confirmation" />
                </div>
            </div>

            <Button
                type="submit"
                class="w-full gap-2"
                size="lg"
                tabindex="5"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                <template v-else>
                    Create account
                    <ArrowRight class="h-4 w-4" />
                </template>
            </Button>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="login()" class="font-medium" :tabindex="6"
                    >Sign in</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
