<template>
    <div>
        <header-breadcrumb :pageTitle="title" :backPath="{ name: 'home' }" />

        <div class="m-3">
            <b-card no-body class="overflow-hidden">
                <div class="row no-gutters row-bordered row-border-light">
                    <div class="col-md-3 pt-0">
                        <b-list-group class="account-settings-links" flush>
                            <b-list-group-item button :active="curTab === 'general'"
                                @click="curTab = 'general'">General</b-list-group-item>
                            <b-list-group-item button :active="curTab === 'password'" @click="curTab = 'password'">Change
                                password</b-list-group-item>
                            <b-list-group-item button :active="curTab === 'pin'" @click="curTab = 'pin'">
                                {{ Trans.get("user.form_profile.label.tab.pin") }}
                            </b-list-group-item>

                            <template v-for="(component, i) in profileTabAdds">
                                <b-list-group-item :key="i" button :active="curTab === component.id"
                                    @click="curTab = component.id"><!-- v-if="AppConfig.packageLocal.moduser.user_profile_tab.profile.show == 1">-->
                                    <!-- {{ AppConfig.packageLocal.moduser.user_profile_tab.profile.caption }} -->
                                    {{ component.caption }}
                                </b-list-group-item>
                            </template>
                        </b-list-group>
                    </div>

                    <!-- Tab General / Profile -->
                    <div class="col-md-9" v-if="curTab === 'general'">
                        <b-card-body class="media align-items-center" v-if="showUserField('avatar')">
                            <!-- <img :src="`${publicUrl}images/avatars/${userForm.id}/${userForm.avatar}`" alt class="d-block ui-w-80" />
                            <div class="media-body ml-4">
                                <b-btn variant="outline-primary">Upload new photo</b-btn>&nbsp;
                                <b-btn variant="default md-btn-flat">Reset</b-btn>
                                <div class="text-light small mt-1">Allowed JPG, GIF or PNG. Max size of 800K</div>
                            </div> -->

                            <image-crop-upload v-if="userForm.id" :imagePath="userForm.avatar"
                                @setValue="userForm.avatar = $event" maxSize="800000"
                                :fieldCaption="Trans.get('user.field_caption.avatar')" fieldName="avatar">
                            </image-crop-upload>
                        </b-card-body>

                        <hr class="border-light m-0" />
                        <b-card-body>
                            <b-form-group label="Username">
                                <b-input v-model="userForm.username" :readonly="true" />
                            </b-form-group>

                            <b-form-group label="Name">
                                <b-input v-model="userForm.name" />
                            </b-form-group>

                            <b-form-group label="Email">
                                <b-input v-model="userForm.email" />
                                <b-alert variant="warning" show class="mt-3 mb-0" v-if="!userForm.email_verified_at && userForm.email == oldEmail
                                    ">
                                    Email Anda belum terverifikasi, silahkan cek email verifikasi
                                    yang kami kirim, atau
                                    <a href="javascript:void(0)" @click="sendVerification(userForm.id)" class="small">
                                        kirim ulang email verifikasi
                                    </a>
                                </b-alert>
                            </b-form-group>

                            <b-form-group label="Phone" v-if="showUserField('phone')">
                                <b-input v-model="userForm.phone" />
                                <b-alert variant="warning" show class="mt-3 mb-0" v-if="false">
                                    Your phone is not confirmed.
                                    <br />
                                    <a href="javascript:void(0)" v-if="false">Resend confirmation</a>
                                </b-alert>
                            </b-form-group>

                            <hr class="border-light m-0" />

                            <!-- <b-form-group label="PIN (6 Digit)">
                                <b-input type="password" :class="{ 'form-control': true, 'is-invalid': $v.userForm.pin.$error ? true : false }" maxlength="6" v-model="userForm.pin" />
                            </b-form-group> -->

                            <div class="text-right mt-3">
                                <b-btn @click="saveUser" variant="primary">Save changes</b-btn>
                            </div>
                        </b-card-body>
                    </div>
                    <!-- / Tab General / Profile -->

                    <!-- Tab Change Password -->
                    <div class="col-md-9" v-if="curTab === 'password'">
                        <b-card-body>
                            <b-form-group label="New password">
                                <b-input type="password" v-model="passwordForm.password" />

                                <small>{{
                                    Trans.get("user.field_description.password")
                                }}</small>
                            </b-form-group>
                            <b-form-group label="Repeat new password">
                                <b-input type="password" v-model="passwordForm.password_confirmation" />
                            </b-form-group>

                            <div class="text-right mt-3">
                                <b-btn @click="savePassword" variant="primary">Save changes</b-btn>
                            </div>
                        </b-card-body>
                    </div>
                    <!-- / Tab Change Password -->

                    <!-- Tab Set PIN -->
                    <div class="col-md-9" v-if="curTab === 'pin'">
                        <b-card-body>
                            <!-- inputan token otp -->
                            <b-form-group :label="Trans.get('user.form_profile.input_caption.token')">
                                <b-input type="text" :class="{
                                    'form-control': true,
                                    'is-invalid': $v.pinForm.token.$error ? true : false,
                                }" maxlength="8" v-model="pinForm.token" />

                                <invalid-tooltip :inputItem="$v.pinForm.token" :fieldName="Trans.get('user.form_profile.input_caption.token')
                                    " />
                            </b-form-group>
                            <!-- . inputan token otp -->

                            <!-- inputan pin baru -->
                            <b-form-group :label="Trans.get('user.form_profile.input_caption.pin')">
                                <b-input type="password" :class="{
                                    'form-control': true,
                                    'is-invalid': $v.pinForm.pin.$error ? true : false,
                                }" maxlength="6" v-model="pinForm.pin" />

                                <invalid-tooltip :inputItem="$v.pinForm.pin"
                                    :fieldName="Trans.get('user.form_profile.input_caption.pin')" />
                            </b-form-group>
                            <!-- / inputan pin baru -->

                            <div class="text-right mt-3" style="display: flex; justify-content: end;">
                                <b-btn @click="sendOtp" variant="primary">Get OTP</b-btn>
                                <b-btn @click="savePin" variant="primary">{{
                                    Trans.get("lang.save_change")
                                }}</b-btn>
                            </div>
                        </b-card-body>
                    </div>
                    <!-- / Tab Set PIN -->

                    <template v-for="(component, i) in profileTabAdds">
                        <div class="col-md-9" v-if="curTab === component.id" :key="i">
                            <component :is="component.component"></component>
                        </div>
                    </template>
                </div>
            </b-card>
        </div>
    </div>
</template>

<style src="node_modules/vue-multiselect/dist/vue-multiselect.min.css"></style>
<style
  src="@/vendor/libs/vue-multiselect/vue-multiselect.scss"
  lang="scss"
></style>
<!-- Page -->
<style src="@/vendor/styles/pages/account.scss" lang="scss"></style>

<script>
import globals from "@/globals";
import Multiselect from "node_modules/vue-multiselect";
import {
    required,
    minLength,
    maxLength,
    email,
} from "node_modules/vuelidate/lib/validators";
import { Encryptor } from "node_modules/node-laravel-encryptor";

export default {
    name: "page-myprofile",
    components: {
        Multiselect,
    },
    computed: {},
    validations() {
        let data = {
            userForm: {
                name: {
                    required,
                },
                email: {
                    email,
                },
                // pin: {
                //     minLength: minLength(6),
                //     maxLength: maxLength(6)
                // }
            },
            pinForm: {
                token: {
                    required,
                    minLength: minLength(8),
                    maxLength: maxLength(8),
                },

                pin: {
                    required,
                    minLength: minLength(6),
                    maxLength: maxLength(6),
                },
            },
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
        curTab: "general",
        passwordForm: {
            password: "",
            password_confirmation: "",
        },
        userForm: {
            id: 0,
            avatar: "",
            name: "",
            username: "",
            email: "",
            verified: true,
            phone: "",
            role: null,
            status: 1,
            profile: {},
            // pin: ""
        },
        pinForm: {
            token: "",
            pin: "",
        },
        oldEmail: "", //data email sebelum diedit
        profileTabAdds: [],
    }),
    computed: {
        // userForm:{
        //   get() {
        //     return this.$store.state.user.userForm;
        //   },
        //   set(value) {
        //     this.$store.commit('userStore/setUserForm', value);
        //   }
        // }
        title() {
            return this.Trans.get("user.my_profile");
        },
    },
    created() {
        this.userForm = this.UserAuth.getUser();
        this.pinForm.token = this.userForm.token;
        this.pinForm.pin = this.userForm.pin;
        this.getUser(this.UserAuth.getUser("id"));
        this.initView();
        var that = this;
        // setTimeout(() => {
        //     that.profileTabAdds.push({
        //         caption: 'Profile',
        //         component: 'profile-tab-profile',
        //         id: 'profile'
        //     });
        // }, 500);
    },
    methods: {
        showUserField(field) {
            return !this.AppConfig.packageLocal.moduser.users_hidden_field.includes(
                field
            );
        },
        showProfileField(field) {
            return !this.AppConfig.packageLocal.moduser.user_profiles.hide.includes(
                field
            );
        },
        //-----
        getUser(id) {
            this.LocalApi.get(this.AppConfig.endpoint.api.moduser + "/" + id).then(
                (res) => {
                    this.userForm = res.data.data;
                    this.userForm.token = "";
                    this.userForm.pin = "";
                    this.oldEmail = this.userForm.email;
                    return res.data.data;
                }
            );
        },
        saveUser(evt) {
            var encryptor = new Encryptor({
                key: this.AppConfig.client.secret_key,
            });
            let data = {
                id: this.userForm.id,
                username: this.userForm.username,
                name: this.userForm.name,
                email: this.userForm.email,
                // pin: this.userForm.pin != '' ? encryptor.encryptSync(this.userForm.pin) : ''
            };

            if (this.showUserField("phone")) data.phone = this.userForm.phone;

            if (this.showUserField("avatar")) data.avatar = this.userForm.avatar;

            var formData = globals().Helper.convertToFormData(data);
            this.LocalApi.post(
                this.AppConfig.endpoint.api.moduser + "/profile",
                formData,
                {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                }
            )
                .then((res) => {
                    this.Web.showAlert({ text: "Profile berhasil disimpan" });
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text:
                            this.Trans.get("alert.update_failed", { attribute: "Data" }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                });
        },
        savePassword() {
            var encryptor = new Encryptor({
                key: this.AppConfig.client.secret_key,
            });
            this.LocalApi.put(
                this.AppConfig.endpoint.api.moduser +
                "/" +
                this.userForm.id +
                "/updatepassword",
                {
                    id: this.userForm.id,
                    password: encryptor.encryptSync(this.passwordForm.password),
                    password_confirmation: encryptor.encryptSync(
                        this.passwordForm.password_confirmation
                    ),
                }
            )
                .then((res) => {
                    this.Web.showAlert({ text: "Password berhasil diganti" });
                    this.passwordForm.password = "";
                    this.passwordForm.password_confirmation = "";
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text:
                            this.Trans.get("alert.update_failed", { attribute: "Data" }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                });
        },
        // funsi untuk send otp
        sendOtp() {
            this.LocalApi.post(this.AppConfig.endpoint.api.moduser + "/sendOtp")
                .then((res) => {
                    this.Web.showAlert({ text: "kode otp telah terkirim" });
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text:
                            this.Trans.get("alert.update_failed", { attribute: "Data" }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                });
        },

        // fungsi untuk mengubah pin
        savePin(evt) {
            evt.preventDefault();
            if (this.$v) {
                this.$v.$touch();
                if (this.$v.$error) {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.form_must_complete_title"),
                        text: this.Trans.get("alert.form_must_complete_text"),
                        type: "warning",
                    });
                    return false;
                }
            }

            var encryptor = new Encryptor({
                key: this.AppConfig.client.secret_key,
            });
            let data = {
                id: this.userForm.id,
                token: this.pinForm.token,
                pin:
                    this.pinForm.pin != "" ? encryptor.encryptSync(this.pinForm.pin) : "",
            };

            var formData = globals().Helper.convertToFormData(data);
            this.LocalApi.post(
                this.AppConfig.endpoint.api.moduser + "/profile",
                formData,
                {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                }
            )
                .then((res) => {
                    this.Web.showAlert({ text: "berhasil ubah pin" });
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text:
                            this.Trans.get("alert.update_failed", { attribute: "Data" }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                });
        },
        sendVerification(userId) {
            this.Web.showAlert({
                styleType: "modal",
                type: "info",
                title: "Confirmation",
                text: "Kirim ulang email verifikasi ?",
                modalButtonCancel: "No",
                modalButtonOk: "Yes",
                onOk: () => {
                    this.$store
                        .dispatch("user/resentVerificationMail", userId)
                        .then((res) => {
                            this.Web.showAlert({ text: "Email berhasil dikirim" });
                        })
                        .catch((res) => {
                            this.Web.showAlert({
                                text: "Email gagal kirim : " + res.message,
                                type: "warning",
                            });
                        });
                },
            });
        },
        initView() {
            this.Web.setModule("moduser");

            this.Web.setNavbarTitle(this.title);
            // this.Web.appendNavbarTitle(this.Trans.chose(this.AppConfig.packageLocal.PSBBI.access.children.module.caption));

            this.Web.resetBreadcrumb();
            this.Web.addBreadcrumb(this.Trans.get("lang.home"));
            this.Web.addBreadcrumb(this.title);

            this.Web.setBodyWithPadding(false);
            this.Web.setShow("moduser");
        },
    },
};
</script>
