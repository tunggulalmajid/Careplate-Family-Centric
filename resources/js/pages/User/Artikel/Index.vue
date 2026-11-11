<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import {
    Search,
    X,
    BookOpen,
    Clock,
    Calendar,
    ArrowRight,
} from 'lucide-vue-next';

interface ArticleItem {
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

interface CategoryItem {
    id: number;
    nama: string;
    slug: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedArticles {
    data: ArticleItem[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    featuredArticle: ArticleItem | null;
    articles: PaginatedArticles;
    categories: CategoryItem[];
    filters: {
        kategori: string;
        search: string;
    };
}>();

const currentCategory = ref(props.filters.kategori || 'semua');
const searchQuery = ref(props.filters.search || '');
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const applyFilter = (category: string) => {
    currentCategory.value = category;
    fetchArticles();
};

const handleSearchInput = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchArticles();
    }, 350);
};

const clearSearch = () => {
    searchQuery.value = '';
    fetchArticles();
};

const resetAllFilters = () => {
    currentCategory.value = 'semua';
    searchQuery.value = '';
    fetchArticles();
};

const fetchArticles = () => {
    router.get(
        route('user.articles'),
        {
            kategori:
                currentCategory.value !== 'semua'
                    ? currentCategory.value
                    : undefined,
            search: searchQuery.value.trim()
                ? searchQuery.value.trim()
                : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

// Fallback image helper
const getPlaceholderImage = (index: number) => {
    const placeholders = [
        'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=800&auto=format&fit=crop&q=80',
        'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&auto=format&fit=crop&q=80',
        'https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=800&auto=format&fit=crop&q=80',
        'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800&auto=format&fit=crop&q=80',
    ];
    return placeholders[index % placeholders.length];
};
</script>

<template>
    <UserLayout title="Artikel">
        <Head title="Artikel Edukasi Gizi - CarePlate" />

        <div class="space-y-8 pb-12">
            <!-- 1. Header Hero Banner (Figma 18:3621) -->
            <div
                class="relative overflow-hidden rounded-3xl border border-orange-100/70 bg-[#fff2eb] p-6 shadow-xs transition-colors sm:p-8 md:p-10 dark:border-[#3d2c26] dark:bg-[#261f1c]"
            >
                <!-- Subtle decorative background circle -->
                <div
                    class="pointer-events-none absolute -top-16 -right-16 h-64 w-64 rounded-full bg-orange-200/30 blur-2xl dark:bg-orange-500/5"
                ></div>

                <div
                    class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center"
                >
                    <div class="max-w-xl">
                        <h1
                            class="font-display text-3xl font-extrabold tracking-tight text-[#191919] sm:text-4xl md:text-5xl dark:text-[#f0efea]"
                        >
                            Artikel
                        </h1>
                        <p
                            class="mt-2 text-sm leading-relaxed text-gray-600 sm:text-base dark:text-[#a3a3a3]"
                        >
                            Bacaan singkat dan panduan praktis seputar pola
                            makan seimbang, resep ramah keluarga, serta edukasi
                            gizi harian.
                        </p>
                    </div>

                    <!-- 3D Bowl / Salad Illustration (Figma 55:10665) -->
                    <div
                        class="flex shrink-0 items-center justify-center md:pr-4"
                    >
                        <div
                            class="relative flex h-28 w-28 items-center justify-center sm:h-32 sm:w-32"
                        >
                            <!-- Stylized SVG Salad Bowl that matches Figma aesthetic -->
                            <svg
                                viewBox="0 0 120 120"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-full w-full drop-shadow-md"
                            >
                                <circle
                                    cx="60"
                                    cy="60"
                                    r="54"
                                    fill="#FFE3D4"
                                    fill-opacity="0.5"
                                    class="dark:fill-[#3a261c]"
                                />
                                <!-- Greens inside bowl -->
                                <ellipse
                                    cx="60"
                                    cy="50"
                                    rx="36"
                                    ry="24"
                                    fill="#3fcb7a"
                                />
                                <circle cx="48" cy="42" r="10" fill="#2eb367" />
                                <circle cx="72" cy="44" r="9" fill="#1b8849" />
                                <circle cx="60" cy="38" r="11" fill="#3fcb7a" />
                                <!-- Tomato slice -->
                                <circle cx="44" cy="48" r="6" fill="#eb4416" />
                                <circle cx="75" cy="48" r="5" fill="#eb4416" />
                                <!-- Egg / protein slice -->
                                <ellipse
                                    cx="60"
                                    cy="48"
                                    rx="8"
                                    ry="6"
                                    fill="#FFFDF5"
                                />
                                <circle
                                    cx="60"
                                    cy="48"
                                    r="3.5"
                                    fill="#FFC933"
                                />
                                <!-- Terracotta / Coral Ceramic Bowl -->
                                <path
                                    d="M20 54C20 76.0914 37.9086 94 60 94C82.0914 94 100 76.0914 100 54H20Z"
                                    fill="#E55934"
                                />
                                <!-- Bowl Rim Highlight -->
                                <ellipse
                                    cx="60"
                                    cy="54"
                                    rx="40"
                                    ry="8"
                                    fill="#F06F4D"
                                />
                                <!-- Bowl Foot Base -->
                                <path
                                    d="M48 94C48 97.3137 53.3726 100 60 100C66.6274 100 72 97.3137 72 94H48Z"
                                    fill="#C73F1C"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Category Tabs & Search Bar Toolbar (Figma 18:3625) -->
            <div
                class="flex flex-col justify-between gap-4 border-b border-gray-200 pb-3 lg:flex-row lg:items-center dark:border-[#2e2e2e]"
            >
                <!-- Categories Bar -->
                <div
                    class="no-scrollbar -mb-3 flex items-center gap-2 overflow-x-auto scroll-smooth py-1"
                >
                    <button
                        type="button"
                        @click="applyFilter('semua')"
                        class="cursor-pointer border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap transition-all"
                        :class="
                            currentCategory === 'semua'
                                ? 'border-[#0b8043] font-bold text-[#0b8043] dark:border-[#3fcb7a] dark:text-[#3fcb7a]'
                                : 'border-transparent text-gray-500 hover:text-gray-900 dark:text-[#a3a3a3] dark:hover:text-[#f0efea]'
                        "
                    >
                        Semua
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        type="button"
                        @click="applyFilter(cat.nama)"
                        class="cursor-pointer border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap transition-all"
                        :class="
                            currentCategory === cat.nama
                                ? 'border-[#0b8043] font-bold text-[#0b8043] dark:border-[#3fcb7a] dark:text-[#3fcb7a]'
                                : 'border-transparent text-gray-500 hover:text-gray-900 dark:text-[#a3a3a3] dark:hover:text-[#f0efea]'
                        "
                    >
                        {{ cat.nama }}
                    </button>
                </div>

                <!-- Integrated Search Bar -->
                <div class="relative w-full shrink-0 lg:w-72">
                    <Search
                        class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-[#737373]"
                    />
                    <input
                        v-model="searchQuery"
                        @input="handleSearchInput"
                        type="text"
                        placeholder="Cari artikel gizi..."
                        class="w-full rounded-xl border border-gray-200 bg-white py-2 pr-9 pl-10 text-sm text-gray-900 placeholder-gray-400 shadow-2xs transition focus:border-[#0b8043] focus:ring-2 focus:ring-[#0b8043]/15 focus:outline-none dark:border-[#2e2e2e] dark:bg-[#1c1c1c] dark:text-[#f0efea] dark:placeholder-[#737373] dark:focus:border-[#3fcb7a]"
                    />
                    <button
                        v-if="searchQuery"
                        type="button"
                        @click="clearSearch"
                        class="absolute top-1/2 right-2.5 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-[#f0efea]"
                        title="Hapus pencarian"
                    >
                        <X class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>

            <!-- 3. Featured Article (Figma 18:3644) -->
            <div
                v-if="featuredArticle"
                class="group overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-xs transition-all hover:shadow-md dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
            >
                <Link
                    :href="route('user.articles.show', featuredArticle.slug)"
                    class="grid min-h-[340px] grid-cols-1 lg:grid-cols-12"
                >
                    <!-- Left Photo Column (Figma 18:3645) -->
                    <div
                        class="relative aspect-16/10 overflow-hidden bg-gray-100 lg:col-span-6 lg:aspect-auto xl:col-span-6 dark:bg-[#262626]"
                    >
                        <img
                            :src="
                                featuredArticle.foto_sampul_url ||
                                getPlaceholderImage(0)
                            "
                            :alt="featuredArticle.judul"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            loading="lazy"
                        />
                        <div
                            class="absolute top-4 left-4 rounded-full bg-black/60 px-3 py-1 text-xs font-semibold text-white backdrop-blur-md lg:hidden"
                        >
                            {{ featuredArticle.kategori || 'Gizi Keluarga' }}
                        </div>
                    </div>

                    <!-- Right Content Column (Figma 18:3646) -->
                    <div
                        class="flex flex-col justify-between p-6 sm:p-8 md:p-10 lg:col-span-6 xl:col-span-6"
                    >
                        <div>
                            <span
                                class="mb-3 hidden text-xs font-bold tracking-wider text-[#0b8043] uppercase lg:inline-block dark:text-[#3fcb7a]"
                            >
                                {{
                                    featuredArticle.kategori || 'Gizi Keluarga'
                                }}
                            </span>

                            <h2
                                class="mb-4 text-2xl leading-tight font-bold tracking-tight text-gray-900 transition-colors group-hover:text-[#0b8043] sm:text-3xl dark:text-[#f0efea] dark:group-hover:text-[#3fcb7a]"
                            >
                                {{ featuredArticle.judul }}
                            </h2>

                            <p
                                v-if="featuredArticle.ringkasan"
                                class="mb-6 line-clamp-3 text-sm leading-relaxed text-gray-600 sm:text-base dark:text-[#a3a3a3]"
                            >
                                {{ featuredArticle.ringkasan }}
                            </p>
                        </div>

                        <!-- Meta Row -->
                        <div
                            class="flex items-center justify-between border-t border-gray-100 pt-4 text-xs text-gray-400 dark:border-[#262626] dark:text-[#737373]"
                        >
                            <div class="flex items-center gap-2">
                                <span>{{ featuredArticle.waktu_baca }}</span>
                                <span>•</span>
                                <span>{{
                                    featuredArticle.tanggal_terbit
                                }}</span>
                            </div>
                            <span
                                class="inline-flex items-center gap-1 font-semibold text-[#0b8043] transition-transform group-hover:translate-x-1 dark:text-[#3fcb7a]"
                            >
                                Baca selengkapnya
                                <ArrowRight class="h-3.5 w-3.5" />
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- 4. Grid of Previous / Other Articles (Figma 18:3651) -->
            <div v-if="articles.data.length > 0" class="space-y-6">
                <div
                    v-if="featuredArticle"
                    class="flex items-center justify-between"
                >
                    <h3
                        class="text-lg font-bold text-gray-900 dark:text-[#f0efea]"
                    >
                        Artikel Lainnya
                    </h3>
                    <span class="text-xs text-gray-400 dark:text-[#737373]">
                        Total {{ articles.total }} artikel
                    </span>
                </div>

                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <article
                        v-for="(item, idx) in articles.data"
                        :key="item.id"
                        class="group flex flex-col overflow-hidden rounded-3xl border border-gray-100 bg-white transition-all hover:shadow-md dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
                    >
                        <Link
                            :href="route('user.articles.show', item.slug)"
                            class="flex h-full flex-col"
                        >
                            <!-- Card Cover Photo -->
                            <div
                                class="relative aspect-16/10 overflow-hidden bg-gray-100 dark:bg-[#262626]"
                            >
                                <img
                                    :src="
                                        item.foto_sampul_url ||
                                        getPlaceholderImage(idx + 1)
                                    "
                                    :alt="item.judul"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    loading="lazy"
                                />
                            </div>

                            <!-- Card Body -->
                            <div
                                class="flex flex-1 flex-col justify-between p-5"
                            >
                                <div>
                                    <span
                                        class="mb-2 block text-xs font-semibold text-[#0b8043] dark:text-[#3fcb7a]"
                                    >
                                        {{ item.kategori || 'Gizi Edukasi' }}
                                    </span>

                                    <h4
                                        class="mb-2 line-clamp-2 text-base leading-snug font-bold text-gray-900 transition-colors group-hover:text-[#0b8043] dark:text-[#f0efea] dark:group-hover:text-[#3fcb7a]"
                                    >
                                        {{ item.judul }}
                                    </h4>

                                    <p
                                        v-if="item.ringkasan"
                                        class="line-clamp-2 text-xs leading-relaxed text-gray-500 dark:text-[#a3a3a3]"
                                    >
                                        {{ item.ringkasan }}
                                    </p>
                                </div>

                                <!-- Card Meta -->
                                <div
                                    class="mt-4 flex items-center justify-between border-t border-gray-50 pt-3 text-xs text-gray-400 dark:border-[#262626] dark:text-[#737373]"
                                >
                                    <span>{{ item.waktu_baca }}</span>
                                    <span>{{ item.tanggal_terbit }}</span>
                                </div>
                            </div>
                        </Link>
                    </article>
                </div>

                <!-- 5. Pagination Bar -->
                <div
                    v-if="articles.links.length > 3"
                    class="flex items-center justify-center gap-1.5 pt-6"
                >
                    <template
                        v-for="(link, index) in articles.links"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="flex h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold transition"
                            :class="
                                link.active
                                    ? 'bg-[#0b8043] text-white shadow-sm dark:bg-[#3fcb7a] dark:text-[#121212]'
                                    : 'border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 dark:border-[#2e2e2e] dark:bg-[#1c1c1c] dark:text-[#a3a3a3] dark:hover:bg-[#262626]'
                            "
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="flex h-9 min-w-9 cursor-not-allowed items-center justify-center rounded-xl border border-gray-100 px-3 text-xs text-gray-300 dark:border-[#262626] dark:text-gray-600"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>

            <!-- Empty State when no articles exist -->
            <div
                v-else-if="!featuredArticle"
                class="rounded-3xl border border-gray-100 bg-white p-8 py-16 text-center dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
            >
                <div
                    class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-[#0b8043] dark:bg-[#17321f] dark:text-[#3fcb7a]"
                >
                    <BookOpen class="h-7 w-7" />
                </div>
                <h3
                    class="mb-1 text-base font-bold text-gray-900 dark:text-[#f0efea]"
                >
                    Tidak ada artikel ditemukan
                </h3>
                <p
                    class="mx-auto mb-5 max-w-md text-xs text-gray-500 sm:text-sm dark:text-[#a3a3a3]"
                >
                    {{
                        searchQuery
                            ? `Tidak ada artikel yang cocok dengan kata kunci "${searchQuery}".`
                            : 'Belum ada artikel yang diterbitkan untuk kategori ini.'
                    }}
                </p>
                <button
                    type="button"
                    @click="resetAllFilters"
                    class="cursor-pointer rounded-xl bg-[#0b8043] px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-[#096836] dark:bg-[#3fcb7a] dark:text-[#121212] dark:hover:bg-[#34aa66]"
                >
                    Reset Filter Pencarian
                </button>
            </div>
        </div>
    </UserLayout>
</template>

<style scoped>
/* Sembunyikan scrollbar horizontal untuk category tab bar */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
