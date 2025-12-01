<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { format } from 'date-fns';
import {
    ArrowLeft,
    Edit,
    Eye,
    MessageSquare,
    Send,
    TrendingUp,
} from 'lucide-vue-next';
import Chip from 'primevue/chip';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import TabPanel from 'primevue/tabpanel';
import TabView from 'primevue/tabview';
import Tag from 'primevue/tag';
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    company_name: string | null;
    tags: string[];
    notes: string | null;
    total_requests_sent: number;
    total_feedbacks: number;
    opted_out: boolean;
    created_at: string;
    last_request_sent_at: string | null;
    last_feedback_at: string | null;
}

interface Feedback {
    id: number;
    rating: number;
    comment: string | null;
    status: string;
    created_at: string;
}

interface ReviewRequest {
    id: number;
    status: string;
    subject: string;
    sent_at: string | null;
    opened_at: string | null;
    completed_at: string | null;
    expires_at: string;
}

interface CommentUser {
    id: number;
    name: string;
    email: string;
}

interface CommentReactionSummary {
    reaction: string;
    count: number;
    reacted_by_current_user: boolean;
}

interface CommentMentionMeta {
    index: number;
    user_id: number;
    name: string;
    position: number;
}

interface Comment {
    id: number;
    body: string;
    created_at: string;
    user: CommentUser;
    parent_id: number | null;
    reactions: CommentReactionSummary[];
    mentions_meta: CommentMentionMeta[];
}

interface MentionableUser {
    id: number;
    name: string;
    email: string;
}

interface Props {
    customer: Customer;
    recentFeedback: Feedback[];
    recentReviewRequests: ReviewRequest[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Customers',
        href: '/customers',
    },
    {
        title: props.customer.name,
        href: `/customers/${props.customer.id}`,
    },
];

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return format(new Date(date), 'MMM d, yyyy h:mm a');
};

const getStatusSeverity = (status: string) => {
    const map: Record<string, any> = {
        pending: 'info',
        opened: 'warning',
        completed: 'success',
        expired: 'danger',
        published: 'success',
        flagged: 'danger',
    };
    return map[status] || 'info';
};

const comments = ref<Comment[]>([]);
const loadingComments = ref(false);
const postingComment = ref(false);
const newCommentBody = ref('');
const replyingTo = ref<Comment | null>(null);

const mentionSuggestions = ref<MentionableUser[]>([]);
const selectedMentionUsers = ref<MentionableUser[]>([]);
const loadingMentions = ref(false);

const commentTextarea = ref<HTMLTextAreaElement | null>(null);

const loadComments = async () => {
    if (loadingComments.value) return;

    loadingComments.value = true;
    try {
        const response = await axios.get(
            `/customers/${props.customer.id}/comments`,
        );
        comments.value = response.data.comments;
    } catch (error) {
        console.error('Failed to load comments', error);
    } finally {
        loadingComments.value = false;
    }
};

const loadMentionSuggestions = async (query: string) => {
    const trimmed = query.trim();

    loadingMentions.value = true;
    try {
        const params = trimmed ? { q: trimmed } : {};

        const response = await axios.get(
            `/customers/${props.customer.id}/comments/mentionable-users`,
            { params },
        );
        mentionSuggestions.value = response.data.users;
    } catch (error) {
        console.error('Failed to load mentionable users', error);
    } finally {
        loadingMentions.value = false;
    }
};

const onCommentInput = (event: Event) => {
    const target = event.target as HTMLTextAreaElement;
    const value = target.value;
    const cursorPos = target.selectionStart ?? value.length;
    const textUpToCursor = value.slice(0, cursorPos);

    const match = textUpToCursor.match(/@([^\s@]{0,30})$/);
    if (match) {
        loadMentionSuggestions(match[1]);
    } else {
        mentionSuggestions.value = [];
    }
};

const addMentionUser = (user: MentionableUser) => {
    const textarea = commentTextarea.value;
    const value = newCommentBody.value;
    const cursorPos = textarea?.selectionStart ?? value.length;
    const textUpToCursor = value.slice(0, cursorPos);
    const match = textUpToCursor.match(/@([^\s@]{0,30})$/);

    if (!match) {
        return;
    }

    const start = cursorPos - match[0].length;
    const before = value.slice(0, start);
    const after = value.slice(cursorPos);
    const insert = `@${user.name} `;
    const nextValue = before + insert + after;

    newCommentBody.value = nextValue;
    mentionSuggestions.value = [];

    if (!selectedMentionUsers.value.find((u) => u.id === user.id)) {
        selectedMentionUsers.value.push(user);
    }

    if (textarea) {
        nextTick(() => {
            const pos = before.length + insert.length;
            textarea.setSelectionRange(pos, pos);
            textarea.focus();
        });
    }
};

const postComment = async (parentId: number | null = null) => {
    if (!newCommentBody.value.trim() || postingComment.value) return;

    postingComment.value = true;
    try {
        const response = await axios.post(
            `/customers/${props.customer.id}/comments`,
            {
                body: newCommentBody.value,
                parent_id: parentId,
                mentions: selectedMentionUsers.value.map((u) => u.id),
            },
        );

        comments.value.push(response.data.comment);
        newCommentBody.value = '';
        selectedMentionUsers.value = [];
        replyingTo.value = null;
    } catch (error) {
        console.error('Failed to post comment', error);
    } finally {
        postingComment.value = false;
    }
};

const childComments = (parentId: number) =>
    comments.value.filter((c) => c.parent_id === parentId);

const rootComments = computed(() =>
    comments.value.filter((c) => c.parent_id === null),
);

const formatCommentBody = (comment: Comment) => {
    let body = comment.body || '';

    if (!comment.mentions_meta || !comment.mentions_meta.length) {
        return body;
    }

    // Replace placeholders like {{1}} with @Name using mentions_meta
    const sortedMeta = [...comment.mentions_meta].sort(
        (a, b) => a.index - b.index,
    );

    for (const meta of sortedMeta) {
        if (meta.index == null || !meta.name) continue;

        const placeholder = `{{${meta.index}}}`;
        const badge = `<span class=\"inline-flex items-center rounded-full bg-muted px-2 py-0.5 text-[11px] text-muted-foreground\">@${meta.name}</span>`;

        while (body.includes(placeholder)) {
            body = body.replace(placeholder, badge);
        }
    }

    return body;
};

const startReply = (comment: Comment) => {
    replyingTo.value = comment;
    newCommentBody.value = '';
    selectedMentionUsers.value = [];
};

const cancelReply = () => {
    replyingTo.value = null;
    newCommentBody.value = '';
    selectedMentionUsers.value = [];
};
const handleCommentsUpdated = (event: Event) => {
    const customEvent = event as CustomEvent<{ customer_id?: number }>;
    const customerId = customEvent.detail?.customer_id;

    console.log(`Event received for customer ${customerId}`);

    if (!customerId || customerId !== props.customer.id) {
        return;
    }

    loadComments();
};

onMounted(() => {
    loadComments();
    window.addEventListener('customer-comments-updated', handleCommentsUpdated);
});

onBeforeUnmount(() => {
    window.removeEventListener(
        'customer-comments-updated',
        handleCommentsUpdated,
    );
});
</script>

<template>
    <Head title="Customer Details" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ customer.name }}
                    </h1>
                    <p class="text-muted-foreground">
                        Customer profile and activity history
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        @click="router.visit(`/customers/${customer.id}/edit`)"
                        variant="outline"
                    >
                        <Edit class="mr-2 h-4 w-4" />
                        Edit
                    </Button>
                    <Button
                        @click="router.visit('/customers')"
                        variant="outline"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Requests</CardTitle
                        >
                        <Send class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ customer.total_requests_sent }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{
                                customer.last_request_sent_at
                                    ? format(
                                          new Date(
                                              customer.last_request_sent_at,
                                          ),
                                          'MMM d, yyyy',
                                      )
                                    : 'Never'
                            }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Feedbacks</CardTitle
                        >
                        <MessageSquare class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ customer.total_feedbacks }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{
                                customer.last_feedback_at
                                    ? format(
                                          new Date(customer.last_feedback_at),
                                          'MMM d, yyyy',
                                      )
                                    : 'Never'
                            }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Status</CardTitle
                        >
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <Tag
                            v-if="customer.opted_out"
                            severity="danger"
                            value="Opted Out"
                        />
                        <Tag v-else severity="success" value="Active" />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Customer Since</CardTitle
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="text-lg font-medium">
                            {{
                                format(
                                    new Date(customer.created_at),
                                    'MMM d, yyyy',
                                )
                            }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Contact Information -->
            <Card>
                <CardHeader>
                    <CardTitle>Contact Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="space-y-1">
                            <p class="text-sm text-muted-foreground">Email</p>
                            <p class="font-medium">{{ customer.email }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm text-muted-foreground">Phone</p>
                            <p class="font-medium">
                                {{ customer.phone || '—' }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm text-muted-foreground">Company</p>
                            <p class="font-medium">
                                {{ customer.company_name || '—' }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="customer.tags && customer.tags.length"
                        class="mt-4"
                    >
                        <p class="mb-2 text-sm text-muted-foreground">Tags</p>
                        <div class="flex flex-wrap gap-2">
                            <Chip
                                v-for="tag in customer.tags"
                                :key="tag"
                                :label="tag"
                            />
                        </div>
                    </div>

                    <div v-if="customer.notes" class="mt-4">
                        <p class="mb-2 text-sm text-muted-foreground">Notes</p>
                        <p class="text-sm whitespace-pre-wrap">
                            {{ customer.notes }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Activity Tabs -->
            <Card>
                <CardContent class="pt-6">
                    <TabView>
                        <TabPanel
                            header="Recent Feedback"
                            value="recent-feedback"
                        >
                            <DataTable
                                :value="recentFeedback"
                                class="p-datatable-sm"
                            >
                                <Column
                                    field="rating"
                                    header="Rating"
                                    class="w-[100px]"
                                >
                                    <template #body="{ data }">
                                        <div class="flex items-center gap-1">
                                            <i
                                                class="pi pi-star-fill text-yellow-500"
                                            ></i>
                                            <span class="font-medium">{{
                                                data.rating
                                            }}</span>
                                        </div>
                                    </template>
                                </Column>

                                <Column field="comment" header="Comment">
                                    <template #body="{ data }">
                                        <div
                                            v-if="data.comment"
                                            class="max-w-md truncate"
                                        >
                                            {{ data.comment }}
                                        </div>
                                        <span v-else class="text-gray-400"
                                            >No comment</span
                                        >
                                    </template>
                                </Column>

                                <Column
                                    field="status"
                                    header="Status"
                                    class="w-[120px]"
                                >
                                    <template #body="{ data }">
                                        <Tag
                                            :severity="
                                                getStatusSeverity(data.status)
                                            "
                                            :value="data.status"
                                            class="capitalize"
                                        />
                                    </template>
                                </Column>

                                <Column
                                    field="created_at"
                                    header="Date"
                                    class="w-[180px]"
                                >
                                    <template #body="{ data }">
                                        {{ formatDate(data.created_at) }}
                                    </template>
                                </Column>

                                <Column header="Actions" class="w-[80px]">
                                    <template #body="{ data }">
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            @click="
                                                router.visit(
                                                    `/feedback/${data.id}`,
                                                )
                                            "
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </template>
                                </Column>

                                <template #empty>
                                    <div class="py-8 text-center text-gray-500">
                                        No feedback submitted yet.
                                    </div>
                                </template>
                            </DataTable>
                        </TabPanel>

                        <TabPanel
                            header="Review Requests"
                            value="review-requests"
                        >
                            <DataTable
                                :value="recentReviewRequests"
                                class="p-datatable-sm"
                            >
                                <Column field="subject" header="Subject">
                                    <template #body="{ data }">
                                        {{ data.subject }}
                                    </template>
                                </Column>

                                <Column
                                    field="status"
                                    header="Status"
                                    class="w-[120px]"
                                >
                                    <template #body="{ data }">
                                        <Tag
                                            :severity="
                                                getStatusSeverity(data.status)
                                            "
                                            :value="data.status"
                                            class="capitalize"
                                        />
                                    </template>
                                </Column>

                                <Column header="Sent" class="w-[180px]">
                                    <template #body="{ data }">
                                        {{ formatDate(data.sent_at) }}
                                    </template>
                                </Column>

                                <Column
                                    header="Opened"
                                    class="hidden w-[180px] md:table-cell"
                                >
                                    <template #body="{ data }">
                                        {{ formatDate(data.opened_at) }}
                                    </template>
                                </Column>

                                <Column header="Actions" class="w-[80px]">
                                    <template #body="{ data }">
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            @click="
                                                router.visit(
                                                    `/review-requests/${data.id}`,
                                                )
                                            "
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </template>
                                </Column>

                                <template #empty>
                                    <div class="py-8 text-center text-gray-500">
                                        No review requests sent yet.
                                    </div>
                                </template>
                            </DataTable>
                        </TabPanel>

                        <TabPanel header="Comments" value="comments">
                            <div class="space-y-6">
                                <!-- New comment editor -->
                                <Card>
                                    <CardContent class="space-y-3 pt-4">
                                        <div
                                            v-if="replyingTo"
                                            class="text-xs text-muted-foreground"
                                        >
                                            Replying to
                                            <span class="font-semibold">
                                                {{ replyingTo.user.name }}
                                            </span>
                                            <button
                                                type="button"
                                                class="ml-2 text-xs underline"
                                                @click="cancelReply"
                                            >
                                                Cancel
                                            </button>
                                        </div>

                                        <div class="space-y-2">
                                            <div class="relative">
                                                <textarea
                                                    ref="commentTextarea"
                                                    v-model="newCommentBody"
                                                    rows="3"
                                                    class="w-full rounded-md border border-gray-300 bg-background p-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none"
                                                    placeholder="Add a comment about this customer... Use @ to mention teammates"
                                                    @input="onCommentInput"
                                                ></textarea>

                                                <div
                                                    v-if="
                                                        mentionSuggestions.length
                                                    "
                                                    class="absolute top-full left-2 z-10 mt-1 w-64 rounded-md border bg-background text-xs shadow-md"
                                                >
                                                    <button
                                                        v-for="user in mentionSuggestions"
                                                        :key="user.id"
                                                        type="button"
                                                        class="flex w-full items-center justify-between px-2 py-1 text-left hover:bg-muted"
                                                        @click="
                                                            addMentionUser(user)
                                                        "
                                                    >
                                                        <span>{{
                                                            user.name
                                                        }}</span>
                                                        <span
                                                            class="text-[10px] text-muted-foreground"
                                                        >
                                                            {{ user.email }}
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex justify-end">
                                            <Button
                                                :disabled="
                                                    postingComment ||
                                                    !newCommentBody.trim()
                                                "
                                                @click="
                                                    postComment(
                                                        replyingTo
                                                            ? replyingTo.id
                                                            : null,
                                                    )
                                                "
                                            >
                                                <MessageSquare
                                                    class="mr-2 h-4 w-4"
                                                />
                                                <span v-if="postingComment"
                                                    >Posting...</span
                                                >
                                                <span v-else>Post Comment</span>
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>

                                <!-- Comments list -->
                                <div
                                    v-if="loadingComments"
                                    class="py-4 text-center text-gray-500"
                                >
                                    Loading comments...
                                </div>
                                <div
                                    v-else-if="!rootComments.length"
                                    class="py-4 text-center text-gray-500"
                                >
                                    No comments yet. Be the first to comment on
                                    this customer.
                                </div>
                                <div v-else class="space-y-3">
                                    <div
                                        v-for="comment in rootComments"
                                        :key="comment.id"
                                        class="rounded-lg border bg-card p-3 text-sm"
                                    >
                                        <div
                                            class="flex items-center justify-between text-xs text-muted-foreground"
                                        >
                                            <span class="font-medium">{{
                                                comment.user.name
                                            }}</span>
                                            <span>{{
                                                formatDate(comment.created_at)
                                            }}</span>
                                        </div>

                                        <div
                                            class="mt-2 flex items-center whitespace-pre-wrap"
                                        >
                                            <div
                                                v-html="
                                                    formatCommentBody(comment)
                                                "
                                            ></div>
                                        </div>

                                        <div
                                            class="mt-2 flex items-center justify-end text-xs text-muted-foreground"
                                        >
                                            <button
                                                type="button"
                                                class="text-xs font-medium"
                                                @click="startReply(comment)"
                                            >
                                                Reply
                                            </button>
                                        </div>

                                        <!-- Child comments -->
                                        <div
                                            v-if="
                                                childComments(comment.id).length
                                            "
                                            class="mt-2 space-y-2 border-l pl-3 text-xs"
                                        >
                                            <div
                                                v-for="child in childComments(
                                                    comment.id,
                                                )"
                                                :key="child.id"
                                                class="rounded-md bg-muted/40 p-2"
                                            >
                                                <div
                                                    class="flex items-center justify-between text-[11px] text-muted-foreground"
                                                >
                                                    <span class="font-medium">
                                                        {{ child.user.name }}
                                                    </span>
                                                    <span>{{
                                                        formatDate(
                                                            child.created_at,
                                                        )
                                                    }}</span>
                                                </div>

                                                <div
                                                    class="mt-1 flex items-center whitespace-pre-wrap"
                                                >
                                                    <div
                                                        v-html="
                                                            formatCommentBody(
                                                                child,
                                                            )
                                                        "
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </TabPanel>
                    </TabView>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
