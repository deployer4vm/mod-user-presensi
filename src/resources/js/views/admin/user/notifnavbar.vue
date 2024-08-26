<template>
    <b-nav-item-dropdown no-caret :right="!isRTL" class="demo-navbar-notifications mr-lg-2">
        <template slot="button-content">
            <i class="fi fi-rr-bell navbar-icon align-middle"></i>
            <span class="badge badge-danger badge-dot indicator" v-if="notif.summary.unread_count != 0"></span>
            <span class="d-lg-none align-middle">&nbsp; {{ Trans.get("notif.notification_title") }}</span>
        </template>

        <div class="bg-primary text-center text-white font-weight-bold p-3" v-if="notif.summary.unread_count != 0">
            {{ notif.summary.unread_count }}
            {{ Trans.get("notif.unread_notification") }}
        </div>

        <b-list-group flush>
            <b-list-group-item class="media d-flex align-items-center" :to="{
                name: item.link_web.route,
                params: item.link_web.parameter,
            }" v-for="item in notif.notification" :key="'navbarnotif-' + item.id">
                <div :class="'ui-icon ui-icon-sm ion ion-ios-text border-0 text-white ' +
                    (item.read_at == null ? 'bg-danger' : 'bg-secondary')
                    "></div>
                <div class="media-body line-height-condenced ml-3">
                    <div :class="item.read_at == null
                            ? 'text-dark font-weight-bold'
                            : 'text-muted'
                        ">
                        {{ item.data.subject }}
                    </div>
                    <div class="small mt-1">{{ item.data.description }}</div>
                    <div class="small mt-1">{{ item.created_at }}</div>
                </div>
            </b-list-group-item>
        </b-list-group>

        <router-link :to="{ name: 'notification' }" class="d-block text-center text-light small p-2 my-1">
            {{ Trans.get("notif.show_all_notification") }}
        </router-link>
    </b-nav-item-dropdown>
</template>

<script>

import { onMessage, getToken } from 'node_modules/firebase/messaging';

export default {
    data() {
        return {
            notif: {
                notification: [],
                summary: {
                    count: 0,
                    unread_count: 0,
                },
            },
            lastNotifCount: 0,
            timeOut: null,
        };
    },
    created() {
        if (this.UserAuth.isLogin()) {
            this.loadNotif();

            if (this.isUsingPusher && this.isNotifPusher) {
                Echo.channel(
                    this.AppConfig.packageLocal.moduser.notification.services
                        .pusher.config.user_channel_prefix +
                    this.UserAuth.getUser("id")
                ).listen(
                    "." +
                    this.AppConfig.packageLocal.moduser.notification
                        .services.pusher.config.user_event,
                    (e) => {
                        this.Web.showAlert({
                            type: "dark",
                            title:
                                this.Trans.get("notif.new_notification_title") +
                                ' <b class="text-danger">(1)</b> ',
                            text:
                                "<br><b>" +
                                e.notification.title +
                                "</b><br>" +
                                e.data.description,
                            position: "default",
                        });
                        this.data.reloadNotif = this.data.reloadNotif
                            ? false
                            : true;
                        this.loadNotif();
                    }
                );
            }

            if (this.isUsingFirebase && this.isNotifFirebase) {
                this.setupFirebaseMessaging();

                this.requestFirebaseToken();
            }
        }
    },
    computed: {
        isUsingPusher() {
            return this.AppConfig.system.broadcast.services_enabled.pusher;
        },
        isNotifPusher() {
            return this.AppConfig.packageLocal.moduser.notification.services.pusher.enable
                ? true
                : false;
        },
        isUsingFirebase() {
            return this.AppConfig.system.broadcast.services_enabled.firebase;
        },
        isNotifFirebase() {
            return this.AppConfig.packageLocal.moduser.notification.services.firebase.enable
                ? true
                : false;
        },
        data: {
            get() {
                return this.$store.state.moduserView.dataNotif;
            },
            set(value) {
                this.$store.commit("moduserView/setDataNotif", value);
            },
        },
    },
    methods: {
        loadNotif() {
            clearTimeout(window.timeOut);
            var that = this;
            let filterParams = { params: { limit: 5 } };
            this.LocalApi.get(
                this.AppConfig.endpoint.api.moduser + "/notification",
                filterParams
            )
                .then((res) => {
                    if (res) {
                        that.notif = res.data.data;
                        var subject = "";
                        var newNotifCount =
                            that.notif.summary.unread_count -
                            that.lastNotifCount;
                        //jika unread notifnya bertambah maka tampilkan notif
                        if (!(this.isUsingPusher && this.isNotifPusher) && !(this.isUsingFirebase && this.isNotifFirebase))
                            if (
                                that.lastNotifCount != 0 &&
                                that.notif.summary.unread_count >
                                that.lastNotifCount
                            ) {
                                var i = 0;
                                _.forEach(that.notif.notification, (v, i) => {
                                    if (v.read_at == null) {
                                        i++;
                                        subject =
                                            subject +
                                            '<div class="p-1 pl-2">' +
                                            v.data.subject +
                                            "</div>";
                                        if (i >= newNotifCount) return true;
                                    }
                                });
                                if (subject != "")
                                    that.Web.showAlert({
                                        type: "dark",
                                        title:
                                            this.Trans.get(
                                                "notif.new_notification_title"
                                            ) +
                                            ' <b class="text-danger">(' +
                                            newNotifCount +
                                            ") </b>",
                                        text: "<br>" + subject,
                                        position: "default",
                                    });
                            }
                        that.lastNotifCount = that.notif.summary.unread_count;
                    }

                    if (!(this.isUsingPusher && this.isNotifPusher) && !(this.isUsingFirebase && this.isNotifFirebase))
                        window.timeOut = setTimeout(function () {
                            that.loadNotif();
                        }, 10000);
                })
                .catch((res) => { });

        },
        setupFirebaseMessaging() {
            this.messaging = window.firebaseMessaging;

            if (Notification.permission === 'granted') {
                onMessage(window.FirebaseMessaging, (payload) => {
                    const notificationTitle = payload.notification?.title;
                    const notificationOptions = {
                        body: payload.notification?.body,
                        icon: payload.notification?.icon || '/assets/images/logo.png',
                        data: payload.data
                    };

                    if ('serviceWorker' in navigator) {
                        navigator.serviceWorker.ready
                            .then((registration) => {
                                registration.showNotification(notificationTitle, notificationOptions);
                                this.Web.showAlert({
                                    type: 'dark',
                                    title: notificationTitle,
                                    text: notificationOptions.body,
                                    position: 'default'
                                });
                            })
                            .catch((error) => {
                                console.error('Service Worker not ready:', error);
                            });
                    }
                });
            } else {
                console.warn('Notification permission not granted or browser does not support notifications.');
            }
        },
        requestFirebaseToken() {
            let projectCode = this.AppConfig.client.project_code;
            let keyToken = 'firebase-messaging-token-' + projectCode;
            let fcmToken = localStorage.getItem(keyToken);

            Notification.requestPermission()
                .then((permission) => {

                    if (permission !== 'granted') {
                        console.error('Notification permission denied.');
                        throw new Error('Notification permission denied');
                    }

                    const firebaseConfig = this.AppConfig.packageLocal.moduser.notification.services.firebase.config.firebasejson;

                    return navigator.serviceWorker.getRegistrations().then((registrations) => {

                        let existingServiceWorker = registrations.find(
                            registration => registration.scope === window.location.origin + '/'
                        );

                        if (existingServiceWorker) {
                            return getToken(window.FirebaseMessaging, { vapidKey: firebaseConfig.vapidKey });
                        } else {
                            return navigator.serviceWorker.register('/firebase-messaging-sw.js')
                                .then((registration) => {
                                    return getToken(window.FirebaseMessaging, { vapidKey: firebaseConfig.vapidKey });
                                });
                        }
                    });
                })
                .then((token) => {

                    if (token) {
                        // Simpan token ke localStorage per project code
                        if (fcmToken !== token) {
                        // update firebase token
                            this.UserAuth.updateFirebaseToken({
                                old_token: fcmToken,
                                new_token: token
                            });
                            localStorage.setItem(keyToken, token);
                        }
                    }
                })
                .catch((error) => {
                    console.error('Unable to get permission to notify.', error);
                });
        }
    },
};
</script>
