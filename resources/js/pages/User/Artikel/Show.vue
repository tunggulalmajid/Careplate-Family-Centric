<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import {
    ArrowLeft,
    Clock,
    Calendar,
    Eye,
    ScanLine,
    ArrowRight,
    Share2,
    CheckCircle2,
} from 'lucide-vue-next';

interface ArticleDetail {
    id: number;
    judul: string;
    slug: string;
    ringkasan: string | null;
    konten: string;
    kategori: string | null;
    foto_sampul_url: string | null;
    nama_penulis: string;
    waktu_baca: string;
    tanggal_terbit: string;
    views: number;
}

interface ArticleRecommendation {
    id: number;
    judul: string;
    slug: string;
    ringkasan: string | null;
    kategori: string | null;
    foto_sampul_url: string | null;
    nama_penulis: string;
    waktu_baca: string;
    tanggal_terbit: string;
    views: number;
}

const props = defineProps<{
    article: ArticleDetail;
    relatedArticles: ArticleRecommendation[];
}>();

const authorInitials = computed(() => {
    if (!props.article.nama_penulis) return 'TR';
    return props.article.nama_penulis
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const defaultCover =
    'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=1200&auto=format&fit=crop&q=80';

const handleShare = async () => {
    if (navigator.share) {
        try {
            await navigator.share({
                title: props.article.judul,
                text: props.article.ringkasan || props.article.judul,
                url: window.location.href,
            });
        } catch {
            // User cancelled or share failed
        }
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Tautan artikel berhasil disalin ke clipboard!');
    }
};
</script>

<template>
    <UserLayout :title="article.judul">
        <Head :title="`${article.judul} - CarePlate`">
            <meta
                name="description"
                :content="article.ringkasan || article.judul"
            />
        </Head>

        <div class="mx-auto max-w-3xl space-y-8 py-4 pb-16 sm:py-6">
            <!-- 1. Back Navigation (Figma 18:3761) -->
            <div class="flex items-center justify-between">
                <Link
                    :href="route('user.articles')"
                    class="group inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-gray-600 transition-colors hover:text-[#0b8043] dark:text-[#a3a3a3] dark:hover:text-[#3fcb7a]"
                >
                    <ArrowLeft
                        class="h-4 w-4 transition-transform group-hover:-translate-x-1"
                    />
                    <span>Artikel</span>
                </Link>

                <button
                    type="button"
                    @click="handleShare"
                    class="cursor-pointer rounded-xl p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-[#a3a3a3] dark:hover:bg-[#262626] dark:hover:text-[#f0efea]"
                    title="Bagikan artikel ini"
                >
                    <Share2 class="h-4 w-4" />
                </button>
            </div>

            <!-- 2. Header: Category & Big Title (Figma 18:3762, 18:3763) -->
            <div class="space-y-3">
                <div class="inline-block">
                    <span
                        class="text-xs font-bold tracking-wider text-[#0b8043] uppercase sm:text-sm dark:text-[#3fcb7a]"
                    >
                        {{ article.kategori || 'Gizi Edukasi' }}
                    </span>
                </div>

                <h1
                    class="text-2xl leading-tight font-extrabold tracking-tight text-gray-900 sm:text-4xl md:text-5xl dark:text-[#f0efea]"
                >
                    {{ article.judul }}
                </h1>
            </div>

            <!-- 3. Author Profile & Reading Meta (Figma 18:3769) -->
            <div class="flex items-center gap-3.5 pb-2">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-emerald-200/60 bg-emerald-100 text-xs font-bold text-[#0b8043] dark:border-[#2e2e2e] dark:bg-[#17321f] dark:text-[#3fcb7a]"
                >
                    {{ authorInitials }}
                </div>
                <div>
                    <div
                        class="text-sm font-semibold text-gray-900 dark:text-[#f0efea]"
                    >
                        {{ article.nama_penulis }}
                    </div>
                    <div
                        class="mt-0.5 flex items-center gap-2 text-xs text-gray-400 dark:text-[#737373]"
                    >
                        <span>{{ article.waktu_baca }}</span>
                        <span>•</span>
                        <span>{{ article.tanggal_terbit }}</span>
                        <span>•</span>
                        <span>{{ article.views }} pembaca</span>
                    </div>
                </div>
            </div>

            <!-- 4. Featured Cover Photo Banner (Figma 18:3770) -->
            <div
                class="relative overflow-hidden rounded-3xl border border-gray-100 bg-gray-100 shadow-xs dark:border-[#2e2e2e] dark:bg-[#262626]"
            >
                <img
                    :src="article.foto_sampul_url || defaultCover"
                    :alt="article.judul"
                    class="aspect-16/9 w-full object-cover sm:max-h-[440px]"
                />
            </div>

            <!-- Excerpt Lead (if present) -->
            <p
                v-if="article.ringkasan"
                class="border-l-4 border-[#0b8043] py-1 pl-4 text-base leading-relaxed font-medium text-gray-600 italic sm:text-lg dark:border-[#3fcb7a] dark:text-[#a3a3a3]"
            >
                {{ article.ringkasan }}
            </p>

            <!-- 5. Full TipTap Article Content (Figma 18:3771) -->
            <div
                class="article-body space-y-4 font-sans text-base leading-relaxed text-gray-800 sm:text-lg dark:text-[#d4d4d4]"
                v-html="article.konten"
            ></div>

            <!-- 6. Call to Action Banner (Figma 18:3793) -->
            <div
                class="mt-10 flex flex-col items-center justify-between gap-4 rounded-2xl border border-orange-100 bg-[#fff2eb] p-4 shadow-2xs transition-colors sm:flex-row sm:p-5 dark:border-[#3d2c26] dark:bg-[#261f1c]"
            >
                <div class="flex items-center gap-3.5 text-center sm:text-left">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-100/80 text-[#eb4416] dark:bg-[#3a261c] dark:text-[#ff7a4d]"
                    >
                        <ScanLine class="h-5 w-5" />
                    </div>
                    <p
                        class="text-sm font-semibold text-gray-900 sm:text-base dark:text-[#f0efea]"
                    >
                        Ragu dengan camilan kemasan di kantin? Pindai labelnya
                        dulu.
                    </p>
                </div>

                <Link
                    :href="route('user.scan')"
                    class="w-full shrink-0 cursor-pointer rounded-full border-2 border-gray-900 px-6 py-2.5 text-center text-xs font-bold text-gray-900 shadow-xs transition-all hover:bg-gray-900 hover:text-white sm:w-auto sm:text-sm dark:border-[#f0efea] dark:text-[#f0efea] dark:hover:bg-[#f0efea] dark:hover:text-[#121212]"
                >
                    Pindai Label
                </Link>
            </div>

            <!-- 7. Related Articles Recommendation Section -->
            <div
                v-if="relatedArticles.length > 0"
                class="space-y-5 border-t border-gray-100 pt-8 dark:border-[#2e2e2e]"
            >
                <div class="flex items-center justify-between">
                    <h3
                        class="text-lg font-bold text-gray-900 dark:text-[#f0efea]"
                    >
                        Rekomendasi Artikel Lainnya
                    </h3>
                    <Link
                        :href="route('user.articles')"
                        class="text-xs font-semibold text-[#0b8043] hover:underline dark:text-[#3fcb7a]"
                    >
                        Lihat Semua
                    </Link>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <article
                        v-for="rel in relatedArticles"
                        :key="rel.id"
                        class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white transition-all hover:shadow-md dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
                    >
                        <Link
                            :href="route('user.articles.show', rel.slug)"
                            class="flex h-full flex-col"
                        >
                            <div
                                class="aspect-16/10 overflow-hidden bg-gray-100 dark:bg-[#262626]"
                            >
                                <img
                                    :src="rel.foto_sampul_url || defaultCover"
                                    :alt="rel.judul"
                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    loading="lazy"
                                />
                            </div>
                            <div
                                class="flex flex-1 flex-col justify-between p-4"
                            >
                                <div>
                                    <span
                                        class="mb-1 block text-[11px] font-semibold text-[#0b8043] dark:text-[#3fcb7a]"
                                    >
                                        {{ rel.kategori || 'Gizi Edukasi' }}
                                    </span>
                                    <h4
                                        class="line-clamp-2 text-sm leading-snug font-bold text-gray-900 transition-colors group-hover:text-[#0b8043] dark:text-[#f0efea] dark:group-hover:text-[#3fcb7a]"
                                    >
                                        {{ rel.judul }}
                                    </h4>
                                </div>
                                <div
                                    class="mt-3 text-[11px] text-gray-400 dark:text-[#737373]"
                                >
                                    {{ rel.waktu_baca }}
                                </div>
                            </div>
                        </Link>
                    </article>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<style>
/* Styling typography rendering untuk konten TipTap artikel */
.article-body h1 {
    font-size: 1.875rem;
    line-height: 2.25rem;
    font-weight: 800;
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #191919;
}

.article-body h2 {
    font-size: 1.5rem;
    line-height: 2rem;
    font-weight: 700;
    margin-top: 1.75rem;
    margin-bottom: 0.75rem;
    color: #191919;
}

.article-body h3 {
    font-size: 1.25rem;
    line-height: 1.75rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    color: #191919;
}

.article-body p {
    margin: 1rem 0;
    line-height: 1.8;
}

.article-body blockquote {
    border-left: 4px solid #0b8043;
    padding-left: 1.25rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #4b5563;
}

.article-body ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin: 1rem 0;
}

.article-body ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin: 1rem 0;
}

.article-body li {
    margin: 0.5rem 0;
}

.article-body hr {
    border: none;
    border-top: 1px solid #e5e7eb;
    margin: 2rem 0;
}

.article-body a {
    color: #0b8043;
    text-decoration: underline;
    text-underline-offset: 3px;
    font-weight: 500;
}

.article-body a:hover {
    color: #096836;
}

.article-body img {
    border-radius: 1.5rem;
    max-width: 100%;
    height: auto;
    margin: 2rem auto;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.article-body iframe {
    border-radius: 1.5rem;
    max-width: 100%;
    margin: 2rem auto;
    aspect-ratio: 16 / 9;
}

/* Dark mode typography */
.dark .article-body h1,
.dark .article-body h2,
.dark .article-body h3,
.dark .article-body strong {
    color: #f0efea !important;
}

.dark .article-body p,
.dark .article-body li {
    color: #d4d4d4 !important;
}

.dark .article-body blockquote {
    border-left-color: #3fcb7a !important;
    color: #a3a3a3 !important;
}

.dark .article-body hr {
    border-top-color: #2e2e2e !important;
}

.dark .article-body a {
    color: #3fcb7a !important;
}

.dark .article-body a:hover {
    color: #34aa66 !important;
}
</style>
