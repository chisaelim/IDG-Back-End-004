<template>
  <div class="card">
    <div class="card-header">
      <div class="d-flex justify-content-between">
        <h3 class="card-title my-auto">{{ props.title }}</h3>
        <div class="d-flex justify-content-end">
          <div class="card-tools">
            <div class="input-group input-group">
              <input
                @focus="maximize"
                v-model="filter"
                type="text"
                class="form-control float-right"
                :placeholder="'Search'"
              />
              <div class="input-group-append">
                <button class="btn btn-default">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card-body table-responsive p-0">
      <table
        class="text-nowrap text-center table-valign-middle table table-head-fixed table-bordered table-hover"
      >
        <thead>
          <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
            <th
              v-for="header in headerGroup.headers"
              :key="header.id"
              :class="{ 'can-sort': header.column.getCanSort() }"
              @click="header.column.getToggleSortingHandler()?.($event)"
            >
              <FlexRender
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
              {{ { asc: " ↓", desc: " ↑" }[header.column.getIsSorted()] }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in table.getRowModel().rows" :key="row.id">
            <td v-for="cell in row.getVisibleCells()" :key="cell.id">
              <FlexRender
                :render="cell.column.columnDef.cell"
                :props="cell.getContext()"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="card-footer clearfix">
      <div class="row">
        <div class="col-md text-nowrap mb-2">
          <div class="d-flex justify-content-between">
            <div class="col-auto my-auto">
              <span
                >Page {{ meta?.current_page }} of {{ meta?.last_page }} -
                {{ meta?.total }} {{ meta?.total !== 1 ? "results" : "result" }}</span
              >
            </div>
            <div class="col-auto">
              <div class="input-group input-group">
                <div class="input-group-prepend">
                  <button class="btn btn-default">Show</button>
                </div>
                <select
                  v-model="pageSize"
                  :disabled="model?.length === 0"
                  class="form-control"
                >
                  <option v-for="size in [5, 10, 25, 50, 100]" :key="size" :value="size">
                    {{ size }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-auto">
          <div class="d-flex justify-content-center">
            <div class="dataTables_paginate paging_simple_numbers">
              <ul class="pagination">
                <li
                  class="paginate_button page-item first"
                  :class="{ disabled: meta?.current_page === 1 }"
                >
                  <a
                    @click="fetchData(meta?.first_page_url)"
                    role="button"
                    tabindex="0"
                    class="page-link"
                    ><i class="fas fa-angle-double-left"></i
                  ></a>
                </li>
                <li
                  class="paginate_button page-item previous"
                  :class="{ disabled: !meta?.prev_page_url }"
                >
                  <a
                    @click="fetchData(meta?.prev_page_url)"
                    role="button"
                    tabindex="0"
                    class="page-link"
                    ><i class="fas fa-angle-left"></i
                  ></a>
                </li>

                <template
                  v-if="model?.length > 0"
                  v-for="{ url, label, active } in meta?.links?.slice(1, -1)"
                  :key="label"
                >
                  <li class="paginate_button page-item" :class="{ active: active }">
                    <a
                      @click="fetchData(url)"
                      role="button"
                      tabindex="0"
                      class="page-link"
                      >{{ label }}</a
                    >
                  </li>
                </template>

                <li
                  class="paginate_button page-item next"
                  :class="{ disabled: !meta?.next_page_url }"
                >
                  <a
                    @click="fetchData(meta?.next_page_url)"
                    role="button"
                    tabindex="0"
                    class="page-link"
                    ><i class="fas fa-angle-right"></i
                  ></a>
                </li>
                <li
                  class="paginate_button page-item last"
                  :class="{ disabled: meta?.current_page === meta?.last_page }"
                >
                  <a
                    @click="fetchData(meta?.last_page_url)"
                    role="button"
                    tabindex="0"
                    class="page-link"
                    ><i class="fas fa-angle-double-right"></i
                  ></a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.can-sort {
  cursor: pointer;
  user-select: none;
}
</style>
<script setup>
import { ref, computed, watch, onBeforeUpdate } from "vue";
import { replaceUnicode } from "@func/text";
import {
  useVueTable,
  FlexRender,
  getCoreRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  getFilteredRowModel,
} from "@tanstack/vue-table";
import { onMounted } from "vue";

const link = defineModel("link", { required: true, type: String });
const model = defineModel({ required: true, type: Array, default: () => [] });
const meta = defineModel("meta", { required: true, type: Object, default: () => ({}) });
const props = defineProps({
  title: String,
  columns: Array,
  pageSize: {
    type: [Number, String],
    default: 25,
    validator: (value) => [5, 10, 25, 50, 100, 250].includes(value),
  },
});

const sorting = ref([]);
const filter = ref("");
const pageIndex = ref(0);
const pageSize = ref(props.pageSize);
const columns = ref(props.columns);
const table = computed(() =>
  useVueTable({
    data: model.value,
    columns: columns.value,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    state: {
      get sorting() {
        return sorting.value;
      },
      get globalFilter() {
        return replaceUnicode(filter.value);
      },
    },
    initialState: {
      pagination: {
        pageIndex: pageIndex.value,
        pageSize: pageSize.value,
      },
    },
    onSortingChange: (updaterOrValue) => {
      sorting.value =
        typeof updaterOrValue === "function"
          ? updaterOrValue(sorting.value)
          : updaterOrValue;
    },
  })
);

onMounted(() => {
  fetchData(link.value);
});

let filterTimeout = null;
watch(filter, async (nv) => {
  if (nv.replace(/\s/g, "") === "") {
    return;
  }
  if (filterTimeout) {
    clearTimeout(filterTimeout);
  }
  filterTimeout = setTimeout(async () => {
    fetchData(link.value);
  }, 1000);
});

let pageTimeout = null;
watch(pageSize, async (nv) => {
  if (pageTimeout) {
    clearTimeout(pageTimeout);
  }
  pageTimeout = setTimeout(async () => {
    fetchData(link.value);
  }, 1000);
});

async function fetchData(url) {
  if (url) {
    const response = await axios.get(url, {
      params: {
        per_page: pageSize.value,
        search: filter.value,
      },
    });
    const { data, meta: newMeta } = response.data;

    link.value = url;
    model.value = data;
    meta.value = newMeta;
  }
}
</script>
