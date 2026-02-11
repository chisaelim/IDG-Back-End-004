<template>
  <div class="card">
    <div class="card-header">
      <div class="d-flex justify-content-between">
        <h3 class="card-title my-auto">{{ props.title }}</h3>
        <div class="d-flex justify-content-end">
          <div class="card-tools">
            <div class="input-group input-group">
              <input
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
          <slot name="actions"></slot>
        </div>
      </div>
    </div>

    <div class="card-body table-responsive p-0">
      <table
        class="text-nowrap table-head-fixed table-valign-middle table table-head-fixed table-bordered table-hover"
      >
        <thead class="text-center">
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
            <td
              v-for="cell in row.getVisibleCells()"
              :key="cell.id"
              :class="`text-${cell.column.columnDef.meta?.align || 'center'}`"
            >
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
                >Page {{ table.getState().pagination.pageIndex + 1 }} of
                {{ table.getPageCount() }} -
                {{ table.getFilteredRowModel().rows.length }} Records</span
              >
            </div>
            <div class="col-auto">
              <div class="input-group input-group">
                <div class="input-group-prepend">
                  <button class="btn btn-default">Show</button>
                </div>
                <select v-model="pageSize" class="form-control">
                  <option
                    v-for="size in [5, 10, 25, 50, 100, 250]"
                    :key="size"
                    :value="size"
                  >
                    {{ size }}
                  </option>

                  <option :value="table.getFilteredRowModel().rows.length">MAX</option>
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
                  class="paginate_button page-item"
                  :class="{ disabled: !table.getCanPreviousPage() }"
                >
                  <a
                    @click="table.setPageIndex(0)"
                    role="button"
                    tabindex="0"
                    class="page-link"
                    ><i class="fas fa-angle-double-left"></i
                  ></a>
                </li>
                <li
                  class="paginate_button page-item"
                  :class="{ disabled: !table.getCanPreviousPage() }"
                >
                  <a
                    @click="table.previousPage()"
                    role="button"
                    tabindex="0"
                    class="page-link"
                    ><i class="fas fa-angle-left"></i
                  ></a>
                </li>

                <li v-if="pageIndex > sidePage" class="paginate_button page-item">
                  <a role="button" tabindex="0" class="page-link">...</a>
                </li>
                <template
                  v-if="table.getPageCount() > 0"
                  v-for="index in table.getPageCount()"
                  :key="index"
                >
                  <li
                    v-if="
                      index > pageIndex - sidePage && index < pageIndex + 2 + sidePage
                    "
                    class="paginate_button page-item"
                    :class="{ active: index - 1 === pageIndex }"
                  >
                    <a
                      @click="table.setPageIndex(index - 1)"
                      role="button"
                      tabindex="0"
                      class="page-link"
                      >{{ index }}</a
                    >
                  </li>
                </template>
                <li
                  v-if="pageIndex + 1 < table.getPageCount() - sidePage"
                  class="paginate_button page-item"
                >
                  <a role="button" tabindex="0" class="page-link">...</a>
                </li>

                <li
                  class="paginate_button page-item"
                  :class="{ disabled: !table.getCanNextPage() }"
                >
                  <a
                    @click="table.nextPage()"
                    role="button"
                    tabindex="0"
                    class="page-link"
                    ><i class="fas fa-angle-right"></i
                  ></a>
                </li>
                <li
                  class="paginate_button page-item"
                  :class="{ disabled: !table.getCanNextPage() }"
                >
                  <a
                    @click="table.setPageIndex(table.getPageCount() - 1)"
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
const props = defineProps({
  title: String,
  data: Array,
  columns: Array,
  pageSize: {
    type: [Number, String],
    default: 25,
    validator: (value) => [5, 10, 25, 50, 100, 250, "MAX"].includes(value),
  },
});
const sidePage = ref(3);

const sorting = ref([]);
const filter = ref("");
const pageIndex = ref(0);
const pageSize = ref(props.pageSize === "MAX" ? props.data.length : props.pageSize);
const columns = ref(props.columns);
const table = computed(() =>
  useVueTable({
    data: props.data,
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

const currentPageIndex = ref(null);
onBeforeUpdate(() => {
  if (filter.value !== "") {
    if (!currentPageIndex.value) {
      currentPageIndex.value = table.value.getState().pagination.pageIndex;
    }
    if (table.value.getPageCount() <= pageIndex.value) {
      pageIndex.value = 0;
    } else {
      pageIndex.value = table.value.getState().pagination.pageIndex;
    }
  } else {
    if (currentPageIndex.value && currentPageIndex.value !== pageIndex.value) {
      pageIndex.value = currentPageIndex.value;
      currentPageIndex.value = null;
    } else {
      pageIndex.value = table.value.getState().pagination.pageIndex;
    }
  }
  columns.value = [...props.columns];
});

watch(pageSize, (nv, ov) => {
  pageIndex.value = 0;
});

watch(
  () => props.data,
  (nv, ov) => {
    if (nv.length !== ov.length) {
      pageIndex.value = 0;
    }
  }
);

watch([() => props.data, () => props.pageSize], (nv, ov) => {
  if (props.pageSize === "MAX") {
    pageSize.value = props.data.length;
  }
});
</script>
