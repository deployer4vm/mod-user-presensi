import authStore from "./modules/auth";
import userStore from "./modules/user";
import roleStore from "./modules/role";
import usersystem from "./modules/usersystem";
import rolesystem from "./modules/rolesystem";
import moduserView from "./storeView";

export default {moduserView, user: userStore,role: roleStore,auth: authStore,usersystem: usersystem,rolesystem: rolesystem};