import authStore from "./modules/auth";
import authenticatorStore from "./modules/authenticator";
import userStore from "./modules/user";
import roleStore from "./modules/role";
import usersystem from "./modules/usersystem";
import rolesystem from "./modules/rolesystem";
import authConfig from "./modules/authConfig";
import moduserView from "./storeView";

export default { 
    moduserView, 
    authConfig,
    authenticator: authenticatorStore,
    user: userStore, 
    role: roleStore, 
    auth: authStore, 
    usersystem: usersystem, 
    rolesystem: rolesystem 
};
