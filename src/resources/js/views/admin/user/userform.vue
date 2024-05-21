<template>
    <div>
        <header-breadcrumb :pageTitle="pageTitle" :backPath="{name: 'user.list'}" />
        <b-container>
            <div class="my-3" v-if="isDataLoaded">
                <b-tabs class="nav-tabs-top nav-responsive-sm">
                    <b-tab :title="Trans.get('user.userform.tab_account_caption')" active>
    
                        <b-card-body v-if="showUserField('avatar')">
                            <!-- <div class="media align-items-center">
                                <div class="ui-w-100 bg-light text-center rounded">
                                    <img :src="`${publicUrl}images/avatars/${form.id}/${form.avatar}?size=100x100`" alt class="d-block" style="max-width: 100px; max-height: 100px;" v-if="form.avatar" />
                                    <div class="ui-w-100 text-center" style="padding-top: 10px;" v-else>
                                        <span class="ion ion-ios-person m-4" style="font-size: 22px"></span>
                                    </div>
                                </div>
                                <div class="media-body ml-3">
                                    <label class="form-label d-block mb-2">{{ Trans.get("user.field_caption.avatar") }}</label>
                                    <b-btn variant="outline-primary" @click="uploadAvatar" size="sm" :disabled="isAdd">Upload new photo</b-btn> &nbsp; <i class="text-muted" v-if="isAdd">Untuk menambahkan avatar, silahkan tambahkan terlebih dahulu data user.</i>
                                    <b-btn variant="default md-btn-flat" @click="resetdAvatar" size="sm" v-if="form.avatar">{{ Trans.get("lang.reset") }}</b-btn>
                                    <div class="text-light small mt-1">Allowed JPG, GIF or PNG. Max size of 800K</div>
                                </div>
                            </div> -->
                            <!-- <image-crop-upload
                                v-if="form.id || isAdd"
                                :imagePath="form.avatar"
                                @setValue="form.avatar = $event"
                                maxSize="800000"
                                :fieldCaption="Trans.get('user.field_caption.avatar')"
                                fieldName="avatar"
                            >
                            </image-crop-upload> -->
                            
                            <!-- Foto -->
                            <b-form-group
                                :label="Trans.get('user.field_caption.avatar')"
                                label-class="pr-md-3"
                                :label-cols-md="2"
                                :content-cols-md="10"
                                label-align-md="right"
                            >
                                <upload-file-list
                                    :files="form.avatar"
                                    @onFiles="form.avatar = $event"
                                    :firstFileAsCover="true"
                                    :canMovePos="false"
                                    :multiple="false"
                                    :title="''"       
                                    :disabled="isDisabled"             
                                />
                            </b-form-group>
                        </b-card-body>
    
                        <b-card-body class="pb-2">
                            <!-- nama -->
                            <b-form-group :label="Trans.get('user.field_caption.name')" label-align-md="right" label-class="pr-md-2" :label-cols-md="2">
                                <b-input :disabled="isDisabled" :state="$v.form.name.$error ? 'invalid' : ''" @blur="$v.form.name.$touch()" v-model="form.name" placeholder="Name" />
                                <invalid-tooltip :inputItem="$v.form.name" :fieldName="'Name'" />
                            </b-form-group>
    
                            <!-- username -->
                            <b-form-group :label="Trans.get('user.field_caption.username')" v-if="showUserField('username')" label-align-md="right" label-class="pr-md-2" :label-cols-md="2">
                                <b-input :disabled="isDisabled" v-model="form.username" :placeholder="Trans.get('user.field_caption.username')" />
                            </b-form-group>
                            
                            <!-- email -->
                            <b-form-group :label="Trans.get('user.field_caption.email')" v-if="showUserField('email')" label-align-md="right" label-class="pr-md-2" :label-cols-md="2">
                                <masked-input :disabled="isDisabled" type="text" :mask="emailMask" :aria-invalid="$v.form.email.$error" v-model.trim="form.email" placeholder="Email" class="form-control" />
                                <a href="javascript:void(0)" class="small" v-if="false">Resend confirmation</a>
                            </b-form-group>
    
                            <!-- telepon -->
                            <b-form-group :label="Trans.get('user.field_caption.phone')" v-if="showUserField('phone')" label-align-md="right" label-class="pr-md-2" :label-cols-md="2">
                                <b-input v-model="form.phone" :disabled="isDisabled" />
                                <a href="javascript:void(0)" class="small" v-if="false">Resend confirmation</a>
                            </b-form-group>
    
                            <!-- <hr class="border-light mt-0" v-if="!isAdd" /> -->
    
                            <!-- pin -->
                            <!-- <b-form-group :label="Trans.get('user.field_caption.pin') + ' ' + Trans.get('user.field_description.pin')" v-if="!isAdd" label-align-md="right" label-class="pr-md-2" :label-cols-md="2">
                                <b-input :disabled="isDisabled" type="password" maxlength="6" v-model="form.pin" />
                            </b-form-group> -->
                        </b-card-body>
    
                        <hr class="border-light m-0" />
    
                        <!-- password & confirm password -->
                        <b-card-body class="pb-2">
                            <b-form-group :label="Trans.get('user.field_caption.password')" label-align-md="right" label-class="pr-md-2" :label-cols-md="2">
                                <b-input :disabled="isDisabled" type="password" :state="$v.form.password.$error ? 'invalid' : ''" v-model.trim="form.password" @blur="$v.form.password.$touch()" :placeholder="Trans.get('user.field_caption.password')" />
                                <invalid-tooltip :inputItem="$v.form.password" :fieldName="Trans.get('user.field_caption.password')" />
                            </b-form-group>
    
                            <b-form-group :label="Trans.get('user.field_caption.confirm_password')" label-align-md="right" label-class="pr-md-2" :label-cols-md="2">
                                <b-input :disabled="isDisabled" type="password" :state="$v.form.repassword.$error ? 'invalid' : ''" v-model.trim="form.repassword" @blur="$v.form.repassword.$touch()" :placeholder="Trans.get('user.field_caption.confirm_password')" />
                                <invalid-tooltip :inputItem="$v.form.repassword" :fieldName="Trans.get('user.field_caption.confirm_password')" :customAlert="{ sameAsPassword: Trans.get('user.alert.password_not_match') }" />
                            </b-form-group>
                        </b-card-body>
    
                        <hr class="border-light m-0" />
                        <b-card-body class="pb-2">
                        
                            <!-- OTP channel -->
                            <b-form-group v-if="authConfig.otp.enable" :label="Trans.get('user.field_caption.otp_channel')" label-align-md="right" label-class="pr-md-2" :label-cols-md="2">
                                <b-select :disabled="isDisabled" v-model="form.otp_channel" :options="selectOtpChannel" class="form-control"/>
                            </b-form-group>
    
                            <!-- User Group -->
                            <b-form-group :label="Trans.get('user.field_caption.user_group_id')" label-align-md="right" label-class="pr-md-2" :label-cols-md="2">
                                <b-select :disabled="isDisabled" v-model="form.user_group_id" :options="selectUserGroup" class="form-control"/>
                            </b-form-group>
    
                            <!-- select role -->
                            <b-form-group :label="Trans.get('user.field_caption.role')" label-align-md="right" label-class="pr-md-2" :label-cols-md="2">
                                <!-- jika multi role -->
                                <template v-if="AppConfig.packageLocal.moduser.user_role.multi_role">
                                    <b-check-group :disabled="isDisabled" :state="$v.form.role_code.$error ? 'invalid' : ''" v-model="form.role_code" :options="roleItems" class="custom-controls-stacked" />
                                </template>
                                <!-- jika single role -->
                                <template v-else>
                                    <b-select :disabled="isDisabled" :state="$v.form.role_code.$error ? 'invalid' : ''" v-model="form.role_code" :options="roleItems" @blur="$v.form.role_code.$touch()" />
                                </template>
                                <invalid-tooltip :inputItem="$v.form.role_code" :fieldName="Trans.get('user.field_caption.role')" />
                            </b-form-group>

                            <!-- main role (khusus jika multi role) -->
                            <b-form-group :label="Trans.get('user.field_caption.main_role')" label-align-md="right" label-class="pr-md-2" :label-cols-md="2" v-if="AppConfig.packageLocal.moduser.user_role.multi_role">
                                <b-select :disabled="isDisabled" v-model="form.main_role_code" :options="selectMainRoleCode" class="form-control"/>
                            </b-form-group>
    
                            <!-- status -->
                            <b-form-group :label="Trans.get('user.field_caption.status')" label-align-md="right" label-class="pr-md-2" :label-cols-md="2">
                                <b-select v-model="form.status" :options="{ 0: Trans.get('user.field_caption.status_item.inactive'), 1: Trans.get('user.field_caption.status_item.active'), 2: Trans.get('user.field_caption.status_item.banned') }" class="form-control"/>
                            </b-form-group>
                        </b-card-body>
    
                    </b-tab>
    
                    <b-tab :title="Trans.get('user.userform.tab_profile_caption')" v-if="showProfile">
                        <b-card-body>
                            -
                        </b-card-body>
                    </b-tab>
                    <b-card-footer>
                        <div class="text-right">
                            <b-btn variant="primary btn-sm w-icon" @click="onSubmit">
                                <i class="fi fi-rs-disk"></i>
                                <span>{{ Trans.get("lang.save_change") }}</span>
                            </b-btn
                            >
                            <!-- <b-btn variant="default">Cancel</b-btn> -->
                        </div>
                    </b-card-footer>
                </b-tabs>
    
            </div>
            <div v-else>
                <div class="text-mutted h-row align-items-center" style="width: 100%;">
                    <div class="col">
                        <div class="sk-cube-grid sk-primary">
                            <div class="sk-cube sk-cube1"></div>
                            <div class="sk-cube sk-cube2"></div>
                            <div class="sk-cube sk-cube3"></div>
                            <div class="sk-cube sk-cube4"></div>
                            <div class="sk-cube sk-cube5"></div>
                            <div class="sk-cube sk-cube6"></div>
                            <div class="sk-cube sk-cube7"></div>
                            <div class="sk-cube sk-cube8"></div>
                            <div class="sk-cube sk-cube9"></div>
                        </div>
                    </div>
                </div>
            </div>
        </b-container>
    </div>
</template>
<!-- Page -->
<style src="@/vendor/styles/pages/users.scss" lang="scss"></style>

<script>
    import MaskedInput, { conformToMask } from "node_modules/vue-text-mask";
    import { emailMask } from "node_modules/text-mask-addons/dist/textMaskAddons";
    import { required, requiredIf, email, sameAs, minLength } from "node_modules/vuelidate/lib/validators";
    import {Encryptor} from "node_modules/node-laravel-encryptor";

    export default {
        name: "moduser-user-form",
        metaInfo() {
            return { title: this.pageTitle };
        },
        components: {
            MaskedInput
        },
        validations() {
            let data = {
                form: {
                    name: {
                        required
                    },
                    email: {
                        email
                    },
                    role_code: {
                        required
                    },
                    password: {
                        required: requiredIf(function(n) {
                            return this.isAdd;
                        }),
                        minLength: minLength(6)
                    },
                    repassword: {
                        sameAsPassword: sameAs("password")
                    }
                }
            };

            // if(this.form.password != '') {
            //     data.form.password = {
            //             required: requiredIf(function(n) {
            //                 return this.isAdd;
            //             }),
            //             minLength: minLength(6)
            //         };
            //     data.form.repassword = {
            //             sameAsPassword: sameAs('password')
            //         };
            // }

            return data;
        },
        data: () => ({
            accessRuleKey: "moduser.user",
            // form
            emailMask: emailMask,
            selectUserGroup: [],
            selectMainRoleCode: [],
            selectOtpChannel: [],
            form: {},
            formEmpty: {
                id: 0,
                name: "",
                password: "",
                repassword: "",
                role_code: [],
                main_role_code: '',
                note: "",
                system_user: 0,
                status: 1,
                // pin: "",
                user_group_id: 0,
                user_type: 1,
                otp_channel: 1,
                profile: {
                    gender: 1,
                    date_of_birth: null,
                    address: "",
                    postal_code: ""
                }
            },
            roleItems: {},
            isDataLoaded: true,
            // upload
            showUpload: false,
            otherParams: {
                token: '123456798',
                name: 'img'
            }
        }),
        computed: {
            isAdd() {
                return this.$route.params.userId ? false : true;
            },
            pageTitle() {
                return this.Trans.chose(this.AppConfig.packageLocal.moduser.access.caption);
            },
            title() {
                return this.isAdd ? this.Trans.get("user.userform.form_add_caption") : ("#" + this.form.name);
            },
            authConfig() {
                return this.$store.state.authConfig.dataConfig;
            },
            oneData: {
                get() {
                    return this.$store.state.user.user;
                },
                set(value) {
                    this.$store.commit("user/setUser", value);
                }
            },
            //access config
            showProfile() {
                return this.AppConfig.packageLocal.moduser.user_profiles.hide_all == 0;
            },
            //
            isDisabled() {
                return false;//this.form.locked_data_mode == 2;
            },
        },
        
        watch: {
            'form.role_code'(v) {
                this.restructSelectMainRole();
            },
        },
        created() {
            if (!(this.UserAuth.hasAccess(this.accessRuleKey, "c") || this.UserAuth.hasAccess(this.accessRuleKey, "u"))) {
                //goto dashboard current tenant
                this.Web.goToCurrentTenant();
                this.Web.showAlert({
                    title: this.Trans.get("alert.warning_title"),
                    text: this.Trans.get("alert.access_denied"),
                    type: "warning"
                });
                return false;
            }

            this.$store.dispatch("authConfig/loadOtp").then(res => {
                this.selectOtpChannel = [];
                if(this.authConfig.otp.enable_channel.email==1){
                    this.selectOtpChannel.push({
                        value: 1,
                        text: this.Trans.get("user.field_caption.otp_channel_select_1"),
                    });
                }
                if(this.authConfig.otp.enable_channel.sms==1){
                    this.selectOtpChannel.push({
                        value: 2,
                        text: this.Trans.get("user.field_caption.otp_channel_select_2"),
                    });
                }
                if(this.authConfig.otp.enable_channel.wa==1){
                    this.selectOtpChannel.push({
                        value: 3,
                        text: this.Trans.get("user.field_caption.otp_channel_select_3"),
                    });
                }
            });
            this.$store.dispatch("authConfig/loadPassword");

            this.loadUserGroup();

            // this.Web.setNavbarTitle(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.caption));
            // this.Web.appendNavbarTitle(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.children.user.caption));

            if (this.showUserField("username")) {
                this.formEmpty.username = "";
            }

            if (this.showUserField("email")) {
                this.formEmpty.email = "";
            }

            if (this.showUserField("avatar")) {
                this.formEmpty.avatar = "";
            }

            if (this.showUserField("phone")) {
                this.formEmpty.phone = "";
            }

            if (this.AppConfig.packageLocal.moduser.user_role.multi_role == 0) {
                this.form.role_code = "";
            }

            //load data role, { system_role: 0 }
            this.$store.dispatch("role/roleList").then(res => {
                this.roleItems = [];
                _.forEach(res.data, (v, i) => {
                    if(!v.role_group || (v.role_group.has_model == 0 && v.role_group.can_selected_on_create==1))
                        this.roleItems.push({ value: v.role_code, text: "[" + v.role_code + "] " + v.name });
                });
                this.restructSelectMainRole();
            });

            this.loadData();
            this.Web.setBodyWithPadding(false);
        },
        methods: {
            loadData() {
                if (!this.isAdd) {
                    this.isDataLoaded = false;
                    this.$store
                        .dispatch("user/getUser", {id: this.$route.params.userId, params: {
                            user_type: 1,
                            append: ['role_group_is_integrated']
                        }})
                        .then(res => {
                            this.checkSystemUser(res)
                            this.form = this.oneData;
                            if (this.AppConfig.packageLocal.moduser.user_role.multi_role) this.form.role_code = [];
                            _.forEach(this.form.user_role, (v, i) => {
                                if (this.AppConfig.packageLocal.moduser.user_role.multi_role) {
                                    this.form.role_code.push(v.role_code);
                                } else {
                                    this.form.role_code = v.role_code;
                                    return true;
                                }
                            });
                            this.form.status = this.form.status==2?2:1;
                            this.form.password = "";
                            this.form.main_role_code = this.form.main_role.role_code;
                            this.form.repassword = "";
                            // this.form.pin = "";
                            this.isDataLoaded = true;
                            delete this.form.user_role;
                            delete this.form.main_role;
                            this.restructSelectMainRole();
                            this.initView();
                        })
                        .catch(res => {
                            this.isDataLoaded = true;
                            this.Web.showAlert({
                                title: this.Trans.get("alert.warning_title"),
                                text: res.message,
                                type: "warning",
                            });
                            this.$router.push({ name: "user.list" });
                        });
                } else {
                    this.isDataLoaded = true;
                    this.form = this.formEmpty;

                    this.initView();
                }
            },
            restructSelectMainRole(){
                this.selectMainRoleCode = [];
                if(this.form.role_code)
                    _.forEach(this.roleItems, (v, i) => {
                        if(this.form.role_code.includes(v.value)){
                            this.selectMainRoleCode.push({ value: v.value, text: v.text  });
                        }
                    });
            },
            onSubmit(evt) {
                evt.preventDefault();
                this.$v.$touch();
                if (this.$v.$invalid) {
                    this.Web.showAlert({
                        type: "danger",
                        title: this.Trans.get("alert.form_must_complete_title"),
                        text: this.Trans.get("alert.form_must_complete_text")
                    });
                } else {
                    var oldPassword = "";
                    var encryptor = new Encryptor({
                        key: this.AppConfig.client.secret_key
                    });

                    if (this.form.password != "") {
                        oldPassword = this.form.password;
                        this.form.password = encryptor.encryptSync(this.form.password);
                        this.form.repassword = encryptor.encryptSync(this.form.repassword);
                    }
                    if (this.isAdd) {
                        if (!this.UserAuth.hasAccess(this.accessRuleKey, "c")) {
                            //goto dashboard current tenant
                            this.Web.goToCurrentTenant();
                            this.Web.showAlert({ text: this.Trans.get("alert.access_denied"), type: "warning" });
                            return false;
                        }

                        this.$store
                            .dispatch("user/register", this.form)
                            .then(res => {
                                this.Web.showAlert({ type: "info", text: "User Registered Successfully" });
                                this.$router.push({ name: "user.list" });
                            })
                            .catch(err => {
                                console.log("add User error : ", err);
                                this.Web.showAlert({ type: "danger", text: "Simpan data gagal : " + err.message });
                            });
                    } else {
                        if (!this.UserAuth.hasAccess(this.accessRuleKey, "u")) {
                            //goto dashboard current tenant
                            this.Web.goToCurrentTenant();
                            this.Web.showAlert({ text: this.Trans.get("alert.access_denied"), type: "warning" });
                            return false;
                        }

                        // var oldPin = "";

                        // if (this.form.pin != '') {
                        //     oldPin = this.form.pin;
                        //     this.form.pin = encryptor.encryptSync(this.form.pin);
                        // }

                        this.$store
                            .dispatch("user/update", { data: this.form, id: this.form.id })
                            .then(res => {
                                this.Web.showAlert({ type: "info", text: "User Updated Successfully" });
                                this.$router.push({ name: "user.list" });
                            })
                            .catch(err => {
                                console.log("update user error : ", err);
                                this.Web.showAlert({ type: "danger", text: "Simpan data gagal : " + err.message });
                            });
                        // this.form.pin = oldPin;
                    }
                    this.form.password = this.form.repassword = oldPassword;
                }
            },
            onReset(evt) {
                evt.preventDefault();
                // Reset our form values
                this.form.name = "";
                this.form.username = "";
                this.form.email = "";
                this.form.phone = "";
                this.form.password = "";
            },
            showUserField(field) {
                return !this.AppConfig.packageLocal.moduser.users_hidden_field.includes(field);
            },
            showProfileField(field) {
                return !this.AppConfig.packageLocal.moduser.user_profiles.hide.includes(field);
            },
            uploadAvatar() {
                this.showUpload = this.showUpload?false:true;
            },
            resetdAvatar() {

            },
            checkSystemUser(user){
                if(user.system_user !== 0 || user.role_group_is_integrated == 1){
                    this.$router.push({ name: "user.list" });
                    this.Web.showAlert({ text: this.Trans.get("alert.access_denied"), type: "warning" });
                }
            },
            loadUserGroup() {         
                this.Repo('moduserUserGroup')
                    .readList({saveState:false})
                    .then((res) => {
                        if (res.count == 0) {
                            this.Web.showAlert({
                                title: this.Trans.get("alert.info_title"),
                                text: this.Trans.get("lang.no_data"),
                                type: "info",
                            });
                        }else{
                            this.selectUserGroup = [{text: '-- Ungrouped --', value: 0},]
                            res.data.forEach((v,i) => {
                                this.selectUserGroup.push({
                                    value: v.id,
                                    text: v.name,
                                });
                            });
                        }
                    })
                    .catch((res) => {
                        this.Web.showAlert({
                            title: this.Trans.get("alert.warning_title"),
                            text:
                                this.Trans.get("alert.read_failed", {
                                    attribute: this.Trans.get('user.user_group.name'),
                                }) +
                                "<br>\n" +
                                res.message,
                            type: "warning",
                        });
                    });
            },
            //--------------------------
            initView() {
                this.Web.setModule("moduser");

                this.Web.setNavbarTitle(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.caption));
                // this.Web.appendNavbarTitle(this.Trans.chose(this.AppConfig.packageLocal.PSBBI.access.children.module.caption));

                this.Web.resetBreadcrumb();
                this.Web.addBreadcrumb(this.Trans.get('lang.home'));
                this.Web.addBreadcrumb(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.caption));
                this.Web.addBreadcrumb(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.children.user.caption),{name:'user.list'});
                this.Web.addBreadcrumb(this.title);

                this.Web.setBodyWithPadding(false);
                this.Web.setShow("moduser");
            },

        }
        // destroyed () {
        //     this.Web.setBodyWithPadding(true);
        // }
    };
</script>
