<template>
  <div>
    <header-breadcrumb :pageTitle="pageTitle" :showBack="false" />
    <b-card class="my-3" no-body>
      <b-card-body>
        <div class="d-flex flex-column flex-md-row justify-content-between">
          <div class="d-flex flex-wrap flex-md-nowrap align-items-center">
            <b-btn
              v-b-toggle.filter-block
              variant="default btn-sm w-icon btn-collapse"
            >
              <i class="fi fi-rs-filter"></i>
              <span>{{ Trans.get("lang.filter") }}</span>
            </b-btn>
            <b-btn
              v-b-toggle.export-block
              variant="default btn-sm w-icon"
              v-if="isConfigExportEnable"
            >
              <i class="fi fi-rs-upload"></i>
              <span>Export</span>
            </b-btn>
          </div>
          <div class="d-flex flex-wrap flex-md-nowrap align-items-center">
            <router-link
              v-if="UserAuth.hasAccess(accessRuleKey + '.group')"
              class="btn btn-sm btn-info w-icon w-50 w-md-auto"
              :to="{ name: 'user.group.list' }"
            >
              <i class="fi fi-rs-users-gear"></i>
              <span>{{ Trans.get("user.user_group.name") }}</span>
            </router-link>
            <router-link
              v-if="UserAuth.hasAccess(accessRuleKey, 'c')"
              class="btn btn-sm btn-primary w-icon w-50 w-md-auto"
              :to="{ name: 'user.add' }"
            >
              <i class="fi fi-rs-add"></i>
              <span>{{ Trans.get("user.userlist.add_new_user") }}</span>
            </router-link>
          </div>
        </div>

        <b-collapse id="filter-block">
          <hr />
          <b-row>
            <b-col md>
              <b-form-group
                :label="Trans.get('pagination.per_page')"
                class="d-inline-block col-md mt-1"
              >
                <b-select
                  v-model="perPage"
                  :options="[10, 20, 30, 40, 50]"
                  class="form-control"
                />
              </b-form-group>
            </b-col>
            <b-col md>
              <b-form-group
                :label="Trans.get('user.field_caption.role')"
                class="d-inline-block col-md mt-1"
              >
                <b-select
                  v-model="filterRole"
                  :options="roleItems"
                  class="form-control"
                />
              </b-form-group>
            </b-col>
            <b-col md>
              <b-form-group
                :label="Trans.get('user.field_caption.status')"
                class="d-inline-block col-md mt-1"
              >
                <b-select
                  v-model="filterStatus"
                  :options="{
                    all: Trans.get('lang.view_all'),
                    '0': Trans.get('user.field_caption.status_item.inactive'),
                    '1': Trans.get('user.field_caption.status_item.active'),
                    '2': Trans.get('user.field_caption.status_item.banned'),
                  }"
                  class="form-control"
                />
              </b-form-group>
            </b-col>
            <b-col md>
              <b-form-group :label="Trans.get('lang.search')">
                <b-input-group>
                  <b-input
                    :placeholder="Trans.get('lang.keyword')"
                    @keyup.enter="doSearch"
                    v-model="searchString"
                  />
                  <b-input-group-append>
                    <b-btn
                      variant="secondary"
                      @click="doSearch"
                      class="btn-icon"
                    >
                      <i class="fi fi-rs-search"></i>
                    </b-btn>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
            </b-col>
          </b-row>
        </b-collapse>

        <!-- block export -->
        <b-collapse id="export-block" v-if="isConfigExportEnable">
          <hr />
          <export
            :api-export-generate="downloadApiUserGenerateUrl"
            :api-export-status="downloadApiUserStatusUrl"
            :adds-jobs-params="null"
          ></export>
        </b-collapse>
      </b-card-body>
    </b-card>

    <b-card class="my-3" no-body>
      <!-- Table controls -->
      <!-- <b-card-body>
                 <div class="d-flex justify-content-end">
                    <div>
                        <b-form-group :label="Trans.get('pagination.per_page')" class="d-inline-block w-auto mt-1">
                            <b-select v-model="perPage" :options="[10, 20, 30, 40, 50]"/>
                        </b-form-group>
                        <b-form-group :label="Trans.get('user.field_caption.role')" class="d-inline-block w-auto mt-1">
                            <b-select v-model="filterRole" :options="roleItems"/>
                        </b-form-group>
                        <b-form-group :label="Trans.get('user.field_caption.status')" class="d-inline-block w-auto mt-1">
                            <b-select v-model="filterStatus" :options="{
                                'all': Trans.get('lang.view_all'),
                                '1': Trans.get('user.field_caption.status_item.active'),
                                '2': Trans.get('user.field_caption.status_item.banned')
                            }"/>
                        </b-form-group>
                        <b-form-group :label="Trans.get('lang.search')" class="d-inline-block w-auto mt-1">
                            <b-input placeholder="Search..." @keyup.enter="doSearch" v-model="searchString" />
                        </b-form-group>
                        <b-form-group :label="''" class="d-inline-block w-auto mt-1">
                            <b-btn variant="info" @click="doSearch" style="margin-top: -3px;">
                                <i class="fi fi-rs-search"></i>
                            </b-btn>
                        </b-form-group>
                    </div>
                    <router-link 
                        v-if="UserAuth.hasAccess(accessRuleKey+'.group')" 
                        class="btn btn-sm btn-info w-icon w-50 w-md-auto"
                        :to="{ name: 'user.group.list' }">
                        <i class="fi fi-rr-list"></i>&nbsp; {{ Trans.get("user.user_group.name") }}
                    </router-link>
                    <router-link 
                        v-if="UserAuth.hasAccess(accessRuleKey, 'c')" 
                        class="btn btn-sm btn-primary w-icon w-50 w-md-auto"
                        :to="{ name: 'user.add' }">
                        <i class="fi fi-rs-add"></i>&nbsp; {{ Trans.get("user.userlist.add_new_user") }}
                    </router-link>
                </div> 
            </b-card-body> -->
      <!-- / Table controls -->

      <b-card-header>
        <div
          class="d-flex justify-content-between align-items-center flex-wrap w-100"
        >
          <h5 class="my-1">List User</h5>
        </div>
      </b-card-header>

      <!-- Table -->
      <div class="table-responsive mb-0">
        <b-table
          :items="listData.data"
          :fields="fields"
          :sort-by.sync="sortBy"
          :sort-desc.sync="sortDesc"
          :striped="true"
          :hover="true"
          :bordered="false"
          class="card-table"
        >
          <template v-slot:cell(account)="data">
            <a href="javascript:void(0)">{{ data.item.account }}</a>
          </template>

          <template v-slot:cell(avatar)="data">
            <a
              :href="publicUrl + 'storage/' + data.item.avatar"
              target="_blank"
              class="d-inline-block ui-w-40 mr-2 rounded-circle overflow-hidden box-avatar bg-transparent"
              style="height: 40px;"
              v-if="data.item.avatar"
            >
              <div class="thumb-img">
                <img :src="publicUrl + 'storage/' + data.item.avatar" />
              </div>
            </a>
            <div
              class="d-inline-block ui-w-40 mr-2 rounded-circle overflow-hidden box-avatar"
              v-else
            >
              <div class="thumb-img">
                <img :src="`${publicUrl}assets/images/avatar.png`" />
              </div>
            </div>
          </template>

          <template v-slot:cell(user_group)="data">
            <b-badge v-if="data.item.user_group" variant="outline-info">{{
              data.item.user_group.name
            }}</b-badge>
            <template v-else>-</template>
          </template>

          <template v-slot:cell(email)="data">
            <div>
              <span>{{ data.item.email }}</span>
              <template v-if="data.item.email_verified_at">
                <b-badge variant="outline-success mt-2"> verified </b-badge>
                <span class="pt-2 text-light">
                  diverifikasi pada
                  {{
                    moment(data.item.email_verified_at).format("YYYY-MM-DD")
                  }}
                  jam {{ moment(data.item.email_verified_at).format("hh:mm") }}
                </span>
              </template>
              <template v-else>
                <b-badge variant="outline-danger mt-2"> unverified </b-badge>
                <div class="pt-2 text-light">
                  kirim ulang email verifikasi ?
                  <b-btn
                    class="btn btn-secondary btn-xs w-icon"
                    @click="sendVerification(data.item.id)"
                    v-if="UserAuth.hasAccess(accessRuleKey, 'u')"
                  >
                    <i class="fi fi-rs-paper-plane"></i>
                    <span>Kirim</span>
                    <!-- kirim -->
                  </b-btn>
                </div>
              </template>
            </div>
          </template>

          <template v-slot:cell(role)="data">
            <b-badge
              variant="outline-info"
              class="m-1"
              v-for="dRole in data.item.roles"
              :key="data.item.id + '-' + dRole.role.id"
              >{{ dRole.role.name }}</b-badge
            >

            <div v-if="data.item.user_role_group">
              <hr class="m-1" />
              <small>Role Group : </small>
              <b-badge
                variant="outline-dark"
                class="m-1"
                v-for="dRole in data.item.user_role_group"
                :key="data.item.id + '-rolegroup-' + dRole.id"
                >{{ dRole.name }}</b-badge
              >
            </div>
          </template>

          <template v-slot:cell(status)="data">
            <b-badge
              variant="outline-secondary"
              v-if="data.item.status === 0"
              >{{
                Trans.get("user.field_caption.status_item.inactive")
              }}</b-badge
            >
            <b-badge variant="outline-success" v-if="data.item.status === 1">{{
              Trans.get("user.field_caption.status_item.active")
            }}</b-badge>
            <b-badge variant="outline-danger" v-if="data.item.status === 2">{{
              Trans.get("user.field_caption.status_item.banned")
            }}</b-badge>
            <!-- <b-badge variant="outline-default" v-if="data.item.status === 0">Guest</b-badge> -->
          </template>

          <template v-slot:cell(actions)="data">
            <div
              class="d-flex align-items-center justify-content-center"
              v-if="!data.item.role_group_is_integrated"
            >
              <!-- <b-btn variant="default btn-xs icon-btn md-btn-flat" v-b-tooltip.hover title="Edit"><i class="ion ion-md-create"></i></b-btn> -->
              <router-link
                class="btn btn-dark icon-btn btn-sm md-btn-flat"
                :title="Trans.get('lang.edit')"
                v-b-tooltip.hover
                :to="{ name: 'user.edit', params: { userId: data.item.id } }"
                v-if="UserAuth.hasAccess(accessRuleKey, 'u')"
              >
                <i class="ion ion-md-create"></i>
              </router-link>
              <b-btn
                class="btn btn-danger icon-btn btn-sm md-btn-flat"
                :title="Trans.get('lang.delete')"
                @click="deleteUser(data.item)"
                v-if="
                  UserAuth.hasAccess(accessRuleKey, 'd') &&
                  data.item.linked_id == 0
                "
                v-b-tooltip.hover
              >
                <i class="ion ion-md-trash"></i>
              </b-btn>
              <!-- <b-dropdown variant="default btn-xs icon-btn md-btn-flat hide-arrow" :right="!isRTL">
                                <template slot="button-content">
                                    <i class="ion ion-ios-settings"></i>
                                </template>
                                <b-dropdown-item href="javascript:void(0)">View profile</b-dropdown-item>
                                <b-dropdown-item @click="banUser(data.item.id)">Ban user</b-dropdown-item>
                                <b-dropdown-item @click="deleteUser(data.item.id)">Remove</b-dropdown-item>
                            </b-dropdown> -->
            </div>
          </template>
        </b-table>
      </div>

      <!-- Pagination -->
      <b-card-footer
        class="flex-md-row flex-column justify-content-center justify-content-md-between align-items-center flex-wrap"
        v-if="listData.count"
      >
        <span class="text-muted"
          >{{
            Trans.get("pagination.page_of_data", {
              curPage: curPage,
              totalPages: totalPages,
              totalData: Format.formatNumber(listData.count),
            })
          }}
        </span>
        <b-pagination
          class="justify-content-center justify-content-sm-end m-0"
          v-model="curPage"
          :total-rows="listData.count"
          :per-page="perPage"
          size="sm"
        />
      </b-card-footer>
      <!-- / Pagination -->
    </b-card>
  </div>
</template>

<style
  src="@/vendor/libs/vue-flatpickr-component/vue-flatpickr-component.scss"
  lang="scss"
></style>

<script>
import flatPickr from "node_modules/vue-flatpickr-component";

export default {
  name: "pages-user-list",
  metaInfo() {
    return { title: this.pageTitle };
  },
  components: {
    flatPickr,
  },
  data: () => ({
    accessRuleKey: "moduser.user",
    // START ----FI listing option
    sortBy: "id",
    sortDesc: false,
    perPage: 10,
    curPage: 1,
    searchString: "",
    loadParams: {},
    roleItems: {},
    filterRole: "all",
    filterStatus: "all",
    // END ---- listing option
    fields: [],
    isLoadingData: false,
    defaultFields: [
      {
        key: "id",
        sortable: true,
        tdClass: "align-middle text-center",
        tdAttr: {
          "data-lable": "ID",
        },
      },
      {
        key: "avatar",
        sortable: true,
        tdClass: "align-middle text-center",
        tdAttr: {
          "data-lable": "Avatar",
        },
      },
      {
        key: "username",
        sortable: true,
        tdClass: "align-middle",
        tdAttr: {
          "data-lable": "Username",
        },
      },
      {
        key: "email",
        sortable: true,
        tdClass: "align-middle",
      },
      {
        key: "name",
        sortable: true,
        tdClass: "align-middle",
        tdAttr: {
          "data-lable": "Name",
        },
      },
      {
        key: "role",
        sortable: true,
        tdClass: "align-middle",
        tdAttr: {
          "data-lable": "Role",
        },
      },
      {
        key: "user_group",
        sortable: true,
        tdClass: "align-middle",
        tdAttr: {
          "data-lable": "User Group",
        },
      },
      {
        key: "status",
        sortable: true,
        tdClass: "align-middle",
        tdAttr: {
          "data-lable": "Status",
        },
      },
      {
        key: "actions",
        label: " ",
        sortable: false,
        tdClass: "text-nowrap text-center",
        thClass: "align-middle text-center",
      },
    ],
  }),

  computed: {
    listData: {
      get() {
        return this.$store.state.user.userList;
      },
      set(value) {
        this.$store.commit("user/setUserList", value);
      },
    },
    pageTitle() {
      return this.Trans.chose(
        this.AppConfig.packageLocal.moduser.access.caption
      );
    },
    listRole() {
      return this.$store.state.role.roleList;
    },
    totalItems() {
      return this.usersData.length;
    },
    totalPages() {
      return Math.ceil(this.listData.count / this.perPage);
    },
    // download
    //------------------------------------------------------------------
    isConfigExportEnable() {
      return this.AppConfig.packageLocal.moduser.export_user.enable == 1;
    },
    downloadApiUserGenerateUrl() {
      return this.AppConfig.endpoint.api.moduser + "/export";
    },
    downloadApiUserStatusUrl() {
      return this.AppConfig.endpoint.api.moduser + "/export/status";
    },
  },
  watch: {
    curPage(v) {
      this.loadData(v, this.searchString, this.sortBy, this.sortDesc);
    },
    perPage(v) {
      this.loadData(
        this.curPage,
        this.searchString,
        this.sortBy,
        this.sortDesc
      );
    },
    sortBy(v) {
      this.loadData(this.curPage, this.searchString, v, this.sortDesc);
    },
    sortDesc(v) {
      this.loadData(this.curPage, this.searchString, this.sortBy, v);
    },
    filterRole(v) {
      this.loadData(
        this.curPage,
        this.searchString,
        this.sortBy,
        this.sortDesc
      );
    },
    filterStatus(v) {
      this.loadData(
        this.curPage,
        this.searchString,
        this.sortBy,
        this.sortDesc
      );
    },
    searchString(v) {
      const val = v.toLowerCase();
      var that = this;
      clearTimeout(this.suggestTimeout);
      this.suggestTimeout = setTimeout(function () {
        that.loadData(1, val);
      }, 300);
    },
  },
  methods: {
    showUserField(field) {
      return !this.AppConfig.packageLocal.moduser.users_hidden_field.includes(
        field
      );
    },
    doSearch() {
      this.loadData(
        this.curPage,
        this.searchString,
        this.sortBy,
        this.sortDesc
      );
    },
    loadData(curPage, q = "", orderBy = false, sortDesc = false) {
      if (this.isLoadingData) return false;

      var offset = this.perPage * (curPage - 1);
      this.loadParams = {};
      this.loadParams.params = {
        append: ["role_group_is_integrated"],
        user_type: 1,
      };

      this.loadParams.params.limit = this.perPage;
      this.loadParams.params.offset = offset;
      this.loadParams.params.system_user = false;

      if (q != "") {
        this.loadParams.params.q = q;
      }

      if (orderBy != false) {
        this.loadParams.params.orderBy = orderBy;
        this.loadParams.params.orderType = sortDesc ? "DESC" : "ASC";
      }

      if (this.filterRole != "all") {
        this.loadParams.params.role = this.filterRole;
      }

      if (this.filterStatus != "all") {
        this.loadParams.params.status = this.filterStatus;
      }

      this.Web.setLoadingPage(true);
      this.isLoadingData = true;

      this.$store.dispatch("user/userList", this.loadParams).then((res) => {
        this.isLoadingData = false;
        this.Web.setLoadingPage(false);
        // _.forEach(res.data,(v,i)=>{
        //     v.roles = v.role.split(';');
        // });
        // this.listData.data = res.data;
        // this.$store.commit("user/setUserList", this.listData);
        // console.log('data : ',this.listData);
      });
    },
    setStatus(userId, status) {
      if (status == 1) {
      } else {
      }
    },
    deleteUser(user) {
      if (!this.UserAuth.hasAccess(this.accessRuleKey, "d")) {
        //goto dashboard current tenant
        this.Web.goToCurrentTenant();
        this.Web.showAlert({
          text: this.Trans.get("alert.access_denied"),
          style: "warning",
        });
        return false;
      }
      if (user.system_user == 1) {
        this.Web.showAlert({
          text: this.Trans.get("alert.access_denied"),
          style: "warning",
        });
        return false;
      }

      this.Web.showAlert({
        styleType: "modal",
        style: "warning",
        title: "Delete Confirmation",
        text: "Are you sure ?",
        modalButtonCancel: "No",
        modalButtonOk: "Yes",
        onOk: () => {
          this.$store
            .dispatch("user/delete", user.id)
            .then((res) => {
              this.Web.showAlert({ text: "Data deleted" });
              this.loadData(1);
            })
            .catch((res) => {
              this.Web.showAlert({ text: "Delete fail", style: "warning" });
            });
        },
      });
    },
    sendVerification(userId) {
      if (!this.UserAuth.hasAccess(this.accessRuleKey, "u")) {
        //goto dashboard current tenant
        this.Web.goToCurrentTenant();
        this.Web.showAlert({
          text: this.Trans.get("alert.access_denied"),
          style: "warning",
        });
        return false;
      }

      this.Web.showAlert({
        styleType: "modal",
        style: "info",
        title: "Confirmation",
        text: "Kirim ulang email verifikasi ?",
        modalButtonCancel: "No",
        modalButtonOk: "Yes",
        onOk: () => {
          this.$store
            .dispatch("user/resentVerificationMail", userId)
            .then((res) => {
              this.Web.showAlert({ text: "Kirim berhasil" });
              this.loadData(1);
            })
            .catch((res) => {
              this.Web.showAlert({
                text: "Kirim gagal : " + res.message,
                style: "warning",
              });
            });
        },
      });
    },
    initView() {
      this.Web.setModule("moduser");

      this.Web.setNavbarTitle(
        this.Trans.chose(this.AppConfig.packageLocal.moduser.access.caption)
      );
      // this.Web.appendNavbarTitle(this.Trans.chose(this.AppConfig.packageLocal.PSBBI.access.children.module.caption));

      this.Web.resetBreadcrumb();
      this.Web.addBreadcrumb(this.Trans.get("lang.home"));
      this.Web.addBreadcrumb(
        this.Trans.chose(this.AppConfig.packageLocal.moduser.access.caption)
      );
      this.Web.addBreadcrumb(
        this.Trans.chose(
          this.AppConfig.packageLocal.moduser.access.children.user.caption
        )
      );
      // this.Web.addBreadcrumb(this.Trans.chose(this.AppConfig.packageLocal.PSBBI.access.children.merchant.children.insurance.caption));

      this.Web.setBodyWithPadding(false);
      this.Web.setShow("moduser");
    },
  },
  created() {
    if (!this.UserAuth.hasAccess(this.accessRuleKey)) {
      //goto dashboard current tenant
      this.Web.goToCurrentTenant();
      this.Web.showAlert({
        title: this.Trans.get("alert.warning_title"),
        text: this.Trans.get("alert.access_denied"),
        type: "warning",
      });
      return false;
    }
    this.initView();

    //load data user
    this.loadData(1);

    //load data role
    this.$store.dispatch("role/roleList").then((res) => {
      let tmpRoleItems = { all: this.Trans.get("lang.view_all") };
      _.forEach(res.data, (v, i) => {
        tmpRoleItems[v.role_code] = "[" + v.role_code + "] " + v.name;
      });
      this.roleItems = tmpRoleItems;
    });

    this.fields = JSON.parse(JSON.stringify(this.defaultFields));

    if (
      !(
        this.UserAuth.hasAccess(this.accessRuleKey, "u") ||
        this.UserAuth.hasAccess(this.accessRuleKey, "d")
      )
    ) {
      this.fields.splice(7, 1);
    }

    //jika username termasuk dari field yang dihide maka hide kolomnya
    if (
      this.AppConfig.packageLocal.moduser.users_hidden_field.includes(
        "username"
      )
    ) {
      this.fields.splice(2, 1);
    }

    if (!this.showUserField("avatar")) {
      this.fields.splice(1, 1);
    }
    //filter kolom table user berdasarkan konfig
    // _.forEach(this.AppConfig.packageLocal.moduser.users_hidden_field,(v,i)=>{
    //     this.fields.splice(5,0,{ key: "stok_optimum", label:"Stok Optimum", sortable: true, thStyle: "min-width: 5rem"})
    // });
  },
};
</script>
