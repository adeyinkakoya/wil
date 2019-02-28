(function () {
'use strict';

/**
 * vuex v3.0.1
 * (c) 2017 Evan You
 * @license MIT
 */
var applyMixin = function (Vue) {
  var version = Number(Vue.version.split('.')[0]);

  if (version >= 2) {
    Vue.mixin({ beforeCreate: vuexInit });
  } else {
    // override init and inject vuex init procedure
    // for 1.x backwards compatibility.
    var _init = Vue.prototype._init;
    Vue.prototype._init = function (options) {
      if ( options === void 0 ) { options = {}; }

      options.init = options.init
        ? [vuexInit].concat(options.init)
        : vuexInit;
      _init.call(this, options);
    };
  }

  /**
   * Vuex init hook, injected into each instances init hooks list.
   */

  function vuexInit () {
    var options = this.$options;
    // store injection
    if (options.store) {
      this.$store = typeof options.store === 'function'
        ? options.store()
        : options.store;
    } else if (options.parent && options.parent.$store) {
      this.$store = options.parent.$store;
    }
  }
};

var devtoolHook =
  typeof window !== 'undefined' &&
  window.__VUE_DEVTOOLS_GLOBAL_HOOK__;

function devtoolPlugin (store) {
  if (!devtoolHook) { return }

  store._devtoolHook = devtoolHook;

  devtoolHook.emit('vuex:init', store);

  devtoolHook.on('vuex:travel-to-state', function (targetState) {
    store.replaceState(targetState);
  });

  store.subscribe(function (mutation, state) {
    devtoolHook.emit('vuex:mutation', mutation, state);
  });
}

/**
 * Get the first item that pass the test
 * by second argument function
 *
 * @param {Array} list
 * @param {Function} f
 * @return {*}
 */
/**
 * Deep copy the given object considering circular structure.
 * This function caches all nested objects and its copies.
 * If it detects circular structure, use cached copy to avoid infinite loop.
 *
 * @param {*} obj
 * @param {Array<Object>} cache
 * @return {*}
 */


/**
 * forEach for object
 */
function forEachValue (obj, fn) {
  Object.keys(obj).forEach(function (key) { return fn(obj[key], key); });
}

function isObject (obj) {
  return obj !== null && typeof obj === 'object'
}

function isPromise (val) {
  return val && typeof val.then === 'function'
}

function assert (condition, msg) {
  if (!condition) { throw new Error(("[vuex] " + msg)) }
}

var Module = function Module (rawModule, runtime) {
  this.runtime = runtime;
  this._children = Object.create(null);
  this._rawModule = rawModule;
  var rawState = rawModule.state;
  this.state = (typeof rawState === 'function' ? rawState() : rawState) || {};
};

var prototypeAccessors$1 = { namespaced: { configurable: true } };

prototypeAccessors$1.namespaced.get = function () {
  return !!this._rawModule.namespaced
};

Module.prototype.addChild = function addChild (key, module) {
  this._children[key] = module;
};

Module.prototype.removeChild = function removeChild (key) {
  delete this._children[key];
};

Module.prototype.getChild = function getChild (key) {
  return this._children[key]
};

Module.prototype.update = function update (rawModule) {
  this._rawModule.namespaced = rawModule.namespaced;
  if (rawModule.actions) {
    this._rawModule.actions = rawModule.actions;
  }
  if (rawModule.mutations) {
    this._rawModule.mutations = rawModule.mutations;
  }
  if (rawModule.getters) {
    this._rawModule.getters = rawModule.getters;
  }
};

Module.prototype.forEachChild = function forEachChild (fn) {
  forEachValue(this._children, fn);
};

Module.prototype.forEachGetter = function forEachGetter (fn) {
  if (this._rawModule.getters) {
    forEachValue(this._rawModule.getters, fn);
  }
};

Module.prototype.forEachAction = function forEachAction (fn) {
  if (this._rawModule.actions) {
    forEachValue(this._rawModule.actions, fn);
  }
};

Module.prototype.forEachMutation = function forEachMutation (fn) {
  if (this._rawModule.mutations) {
    forEachValue(this._rawModule.mutations, fn);
  }
};

Object.defineProperties( Module.prototype, prototypeAccessors$1 );

var ModuleCollection = function ModuleCollection (rawRootModule) {
  // register root module (Vuex.Store options)
  this.register([], rawRootModule, false);
};

ModuleCollection.prototype.get = function get (path) {
  return path.reduce(function (module, key) {
    return module.getChild(key)
  }, this.root)
};

ModuleCollection.prototype.getNamespace = function getNamespace (path) {
  var module = this.root;
  return path.reduce(function (namespace, key) {
    module = module.getChild(key);
    return namespace + (module.namespaced ? key + '/' : '')
  }, '')
};

ModuleCollection.prototype.update = function update$1 (rawRootModule) {
  update([], this.root, rawRootModule);
};

ModuleCollection.prototype.register = function register (path, rawModule, runtime) {
    var this$1 = this;
    if ( runtime === void 0 ) { runtime = true; }

  if (process.env.NODE_ENV !== 'production') {
    assertRawModule(path, rawModule);
  }

  var newModule = new Module(rawModule, runtime);
  if (path.length === 0) {
    this.root = newModule;
  } else {
    var parent = this.get(path.slice(0, -1));
    parent.addChild(path[path.length - 1], newModule);
  }

  // register nested modules
  if (rawModule.modules) {
    forEachValue(rawModule.modules, function (rawChildModule, key) {
      this$1.register(path.concat(key), rawChildModule, runtime);
    });
  }
};

ModuleCollection.prototype.unregister = function unregister (path) {
  var parent = this.get(path.slice(0, -1));
  var key = path[path.length - 1];
  if (!parent.getChild(key).runtime) { return }

  parent.removeChild(key);
};

function update (path, targetModule, newModule) {
  if (process.env.NODE_ENV !== 'production') {
    assertRawModule(path, newModule);
  }

  // update target module
  targetModule.update(newModule);

  // update nested modules
  if (newModule.modules) {
    for (var key in newModule.modules) {
      if (!targetModule.getChild(key)) {
        if (process.env.NODE_ENV !== 'production') {
          console.warn(
            "[vuex] trying to add a new module '" + key + "' on hot reloading, " +
            'manual reload is needed'
          );
        }
        return
      }
      update(
        path.concat(key),
        targetModule.getChild(key),
        newModule.modules[key]
      );
    }
  }
}

var functionAssert = {
  assert: function (value) { return typeof value === 'function'; },
  expected: 'function'
};

var objectAssert = {
  assert: function (value) { return typeof value === 'function' ||
    (typeof value === 'object' && typeof value.handler === 'function'); },
  expected: 'function or object with "handler" function'
};

var assertTypes = {
  getters: functionAssert,
  mutations: functionAssert,
  actions: objectAssert
};

function assertRawModule (path, rawModule) {
  Object.keys(assertTypes).forEach(function (key) {
    if (!rawModule[key]) { return }

    var assertOptions = assertTypes[key];

    forEachValue(rawModule[key], function (value, type) {
      assert(
        assertOptions.assert(value),
        makeAssertionMessage(path, key, type, value, assertOptions.expected)
      );
    });
  });
}

function makeAssertionMessage (path, key, type, value, expected) {
  var buf = key + " should be " + expected + " but \"" + key + "." + type + "\"";
  if (path.length > 0) {
    buf += " in module \"" + (path.join('.')) + "\"";
  }
  buf += " is " + (JSON.stringify(value)) + ".";
  return buf
}

var Vue$1; // bind on install

var Store = function Store (options) {
  var this$1 = this;
  if ( options === void 0 ) { options = {}; }

  // Auto install if it is not done yet and `window` has `Vue`.
  // To allow users to avoid auto-installation in some cases,
  // this code should be placed here. See #731
  if (!Vue$1 && typeof window !== 'undefined' && window.Vue) {
    install(window.Vue);
  }

  if (process.env.NODE_ENV !== 'production') {
    assert(Vue$1, "must call Vue.use(Vuex) before creating a store instance.");
    assert(typeof Promise !== 'undefined', "vuex requires a Promise polyfill in this browser.");
    assert(this instanceof Store, "Store must be called with the new operator.");
  }

  var plugins = options.plugins; if ( plugins === void 0 ) { plugins = []; }
  var strict = options.strict; if ( strict === void 0 ) { strict = false; }

  var state = options.state; if ( state === void 0 ) { state = {}; }
  if (typeof state === 'function') {
    state = state() || {};
  }

  // store internal state
  this._committing = false;
  this._actions = Object.create(null);
  this._actionSubscribers = [];
  this._mutations = Object.create(null);
  this._wrappedGetters = Object.create(null);
  this._modules = new ModuleCollection(options);
  this._modulesNamespaceMap = Object.create(null);
  this._subscribers = [];
  this._watcherVM = new Vue$1();

  // bind commit and dispatch to self
  var store = this;
  var ref = this;
  var dispatch = ref.dispatch;
  var commit = ref.commit;
  this.dispatch = function boundDispatch (type, payload) {
    return dispatch.call(store, type, payload)
  };
  this.commit = function boundCommit (type, payload, options) {
    return commit.call(store, type, payload, options)
  };

  // strict mode
  this.strict = strict;

  // init root module.
  // this also recursively registers all sub-modules
  // and collects all module getters inside this._wrappedGetters
  installModule(this, state, [], this._modules.root);

  // initialize the store vm, which is responsible for the reactivity
  // (also registers _wrappedGetters as computed properties)
  resetStoreVM(this, state);

  // apply plugins
  plugins.forEach(function (plugin) { return plugin(this$1); });

  if (Vue$1.config.devtools) {
    devtoolPlugin(this);
  }
};

var prototypeAccessors = { state: { configurable: true } };

prototypeAccessors.state.get = function () {
  return this._vm._data.$$state
};

prototypeAccessors.state.set = function (v) {
  if (process.env.NODE_ENV !== 'production') {
    assert(false, "Use store.replaceState() to explicit replace store state.");
  }
};

Store.prototype.commit = function commit (_type, _payload, _options) {
    var this$1 = this;

  // check object-style commit
  var ref = unifyObjectStyle(_type, _payload, _options);
    var type = ref.type;
    var payload = ref.payload;
    var options = ref.options;

  var mutation = { type: type, payload: payload };
  var entry = this._mutations[type];
  if (!entry) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(("[vuex] unknown mutation type: " + type));
    }
    return
  }
  this._withCommit(function () {
    entry.forEach(function commitIterator (handler) {
      handler(payload);
    });
  });
  this._subscribers.forEach(function (sub) { return sub(mutation, this$1.state); });

  if (
    process.env.NODE_ENV !== 'production' &&
    options && options.silent
  ) {
    console.warn(
      "[vuex] mutation type: " + type + ". Silent option has been removed. " +
      'Use the filter functionality in the vue-devtools'
    );
  }
};

Store.prototype.dispatch = function dispatch (_type, _payload) {
    var this$1 = this;

  // check object-style dispatch
  var ref = unifyObjectStyle(_type, _payload);
    var type = ref.type;
    var payload = ref.payload;

  var action = { type: type, payload: payload };
  var entry = this._actions[type];
  if (!entry) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(("[vuex] unknown action type: " + type));
    }
    return
  }

  this._actionSubscribers.forEach(function (sub) { return sub(action, this$1.state); });

  return entry.length > 1
    ? Promise.all(entry.map(function (handler) { return handler(payload); }))
    : entry[0](payload)
};

Store.prototype.subscribe = function subscribe (fn) {
  return genericSubscribe(fn, this._subscribers)
};

Store.prototype.subscribeAction = function subscribeAction (fn) {
  return genericSubscribe(fn, this._actionSubscribers)
};

Store.prototype.watch = function watch (getter, cb, options) {
    var this$1 = this;

  if (process.env.NODE_ENV !== 'production') {
    assert(typeof getter === 'function', "store.watch only accepts a function.");
  }
  return this._watcherVM.$watch(function () { return getter(this$1.state, this$1.getters); }, cb, options)
};

Store.prototype.replaceState = function replaceState (state) {
    var this$1 = this;

  this._withCommit(function () {
    this$1._vm._data.$$state = state;
  });
};

Store.prototype.registerModule = function registerModule (path, rawModule, options) {
    if ( options === void 0 ) { options = {}; }

  if (typeof path === 'string') { path = [path]; }

  if (process.env.NODE_ENV !== 'production') {
    assert(Array.isArray(path), "module path must be a string or an Array.");
    assert(path.length > 0, 'cannot register the root module by using registerModule.');
  }

  this._modules.register(path, rawModule);
  installModule(this, this.state, path, this._modules.get(path), options.preserveState);
  // reset store to update getters...
  resetStoreVM(this, this.state);
};

Store.prototype.unregisterModule = function unregisterModule (path) {
    var this$1 = this;

  if (typeof path === 'string') { path = [path]; }

  if (process.env.NODE_ENV !== 'production') {
    assert(Array.isArray(path), "module path must be a string or an Array.");
  }

  this._modules.unregister(path);
  this._withCommit(function () {
    var parentState = getNestedState(this$1.state, path.slice(0, -1));
    Vue$1.delete(parentState, path[path.length - 1]);
  });
  resetStore(this);
};

Store.prototype.hotUpdate = function hotUpdate (newOptions) {
  this._modules.update(newOptions);
  resetStore(this, true);
};

Store.prototype._withCommit = function _withCommit (fn) {
  var committing = this._committing;
  this._committing = true;
  fn();
  this._committing = committing;
};

Object.defineProperties( Store.prototype, prototypeAccessors );

function genericSubscribe (fn, subs) {
  if (subs.indexOf(fn) < 0) {
    subs.push(fn);
  }
  return function () {
    var i = subs.indexOf(fn);
    if (i > -1) {
      subs.splice(i, 1);
    }
  }
}

function resetStore (store, hot) {
  store._actions = Object.create(null);
  store._mutations = Object.create(null);
  store._wrappedGetters = Object.create(null);
  store._modulesNamespaceMap = Object.create(null);
  var state = store.state;
  // init all modules
  installModule(store, state, [], store._modules.root, true);
  // reset vm
  resetStoreVM(store, state, hot);
}

function resetStoreVM (store, state, hot) {
  var oldVm = store._vm;

  // bind store public getters
  store.getters = {};
  var wrappedGetters = store._wrappedGetters;
  var computed = {};
  forEachValue(wrappedGetters, function (fn, key) {
    // use computed to leverage its lazy-caching mechanism
    computed[key] = function () { return fn(store); };
    Object.defineProperty(store.getters, key, {
      get: function () { return store._vm[key]; },
      enumerable: true // for local getters
    });
  });

  // use a Vue instance to store the state tree
  // suppress warnings just in case the user has added
  // some funky global mixins
  var silent = Vue$1.config.silent;
  Vue$1.config.silent = true;
  store._vm = new Vue$1({
    data: {
      $$state: state
    },
    computed: computed
  });
  Vue$1.config.silent = silent;

  // enable strict mode for new vm
  if (store.strict) {
    enableStrictMode(store);
  }

  if (oldVm) {
    if (hot) {
      // dispatch changes in all subscribed watchers
      // to force getter re-evaluation for hot reloading.
      store._withCommit(function () {
        oldVm._data.$$state = null;
      });
    }
    Vue$1.nextTick(function () { return oldVm.$destroy(); });
  }
}

function installModule (store, rootState, path, module, hot) {
  var isRoot = !path.length;
  var namespace = store._modules.getNamespace(path);

  // register in namespace map
  if (module.namespaced) {
    store._modulesNamespaceMap[namespace] = module;
  }

  // set state
  if (!isRoot && !hot) {
    var parentState = getNestedState(rootState, path.slice(0, -1));
    var moduleName = path[path.length - 1];
    store._withCommit(function () {
      Vue$1.set(parentState, moduleName, module.state);
    });
  }

  var local = module.context = makeLocalContext(store, namespace, path);

  module.forEachMutation(function (mutation, key) {
    var namespacedType = namespace + key;
    registerMutation(store, namespacedType, mutation, local);
  });

  module.forEachAction(function (action, key) {
    var type = action.root ? key : namespace + key;
    var handler = action.handler || action;
    registerAction(store, type, handler, local);
  });

  module.forEachGetter(function (getter, key) {
    var namespacedType = namespace + key;
    registerGetter(store, namespacedType, getter, local);
  });

  module.forEachChild(function (child, key) {
    installModule(store, rootState, path.concat(key), child, hot);
  });
}

/**
 * make localized dispatch, commit, getters and state
 * if there is no namespace, just use root ones
 */
function makeLocalContext (store, namespace, path) {
  var noNamespace = namespace === '';

  var local = {
    dispatch: noNamespace ? store.dispatch : function (_type, _payload, _options) {
      var args = unifyObjectStyle(_type, _payload, _options);
      var payload = args.payload;
      var options = args.options;
      var type = args.type;

      if (!options || !options.root) {
        type = namespace + type;
        if (process.env.NODE_ENV !== 'production' && !store._actions[type]) {
          console.error(("[vuex] unknown local action type: " + (args.type) + ", global type: " + type));
          return
        }
      }

      return store.dispatch(type, payload)
    },

    commit: noNamespace ? store.commit : function (_type, _payload, _options) {
      var args = unifyObjectStyle(_type, _payload, _options);
      var payload = args.payload;
      var options = args.options;
      var type = args.type;

      if (!options || !options.root) {
        type = namespace + type;
        if (process.env.NODE_ENV !== 'production' && !store._mutations[type]) {
          console.error(("[vuex] unknown local mutation type: " + (args.type) + ", global type: " + type));
          return
        }
      }

      store.commit(type, payload, options);
    }
  };

  // getters and state object must be gotten lazily
  // because they will be changed by vm update
  Object.defineProperties(local, {
    getters: {
      get: noNamespace
        ? function () { return store.getters; }
        : function () { return makeLocalGetters(store, namespace); }
    },
    state: {
      get: function () { return getNestedState(store.state, path); }
    }
  });

  return local
}

function makeLocalGetters (store, namespace) {
  var gettersProxy = {};

  var splitPos = namespace.length;
  Object.keys(store.getters).forEach(function (type) {
    // skip if the target getter is not match this namespace
    if (type.slice(0, splitPos) !== namespace) { return }

    // extract local getter type
    var localType = type.slice(splitPos);

    // Add a port to the getters proxy.
    // Define as getter property because
    // we do not want to evaluate the getters in this time.
    Object.defineProperty(gettersProxy, localType, {
      get: function () { return store.getters[type]; },
      enumerable: true
    });
  });

  return gettersProxy
}

function registerMutation (store, type, handler, local) {
  var entry = store._mutations[type] || (store._mutations[type] = []);
  entry.push(function wrappedMutationHandler (payload) {
    handler.call(store, local.state, payload);
  });
}

function registerAction (store, type, handler, local) {
  var entry = store._actions[type] || (store._actions[type] = []);
  entry.push(function wrappedActionHandler (payload, cb) {
    var res = handler.call(store, {
      dispatch: local.dispatch,
      commit: local.commit,
      getters: local.getters,
      state: local.state,
      rootGetters: store.getters,
      rootState: store.state
    }, payload, cb);
    if (!isPromise(res)) {
      res = Promise.resolve(res);
    }
    if (store._devtoolHook) {
      return res.catch(function (err) {
        store._devtoolHook.emit('vuex:error', err);
        throw err
      })
    } else {
      return res
    }
  });
}

function registerGetter (store, type, rawGetter, local) {
  if (store._wrappedGetters[type]) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(("[vuex] duplicate getter key: " + type));
    }
    return
  }
  store._wrappedGetters[type] = function wrappedGetter (store) {
    return rawGetter(
      local.state, // local state
      local.getters, // local getters
      store.state, // root state
      store.getters // root getters
    )
  };
}

function enableStrictMode (store) {
  store._vm.$watch(function () { return this._data.$$state }, function () {
    if (process.env.NODE_ENV !== 'production') {
      assert(store._committing, "Do not mutate vuex store state outside mutation handlers.");
    }
  }, { deep: true, sync: true });
}

function getNestedState (state, path) {
  return path.length
    ? path.reduce(function (state, key) { return state[key]; }, state)
    : state
}

function unifyObjectStyle (type, payload, options) {
  if (isObject(type) && type.type) {
    options = payload;
    payload = type;
    type = type.type;
  }

  if (process.env.NODE_ENV !== 'production') {
    assert(typeof type === 'string', ("Expects string as the type, but found " + (typeof type) + "."));
  }

  return { type: type, payload: payload, options: options }
}

function install (_Vue) {
  if (Vue$1 && _Vue === Vue$1) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(
        '[vuex] already installed. Vue.use(Vuex) should be called only once.'
      );
    }
    return
  }
  Vue$1 = _Vue;
  applyMixin(Vue$1);
}

var WilokeInput = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.isRequired(_vm.settings.isRequired))?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text"},domProps:{"value":(_vm.value)},on:{"keyup":_vm.changed,"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text"},domProps:{"value":(_vm.value)},on:{"keyup":_vm.changed,"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis",class:{'required': _vm.isRequired(_vm.settings.isRequired)}},[_vm._v(_vm._s(_vm.settings.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})]),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.settings.errMsg!=''),expression:"settings.errMsg!=''"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))])])},staticRenderFns: [],
    data: function data(){
        return {
          value: typeof this.settings.value !== 'undefined' ? this.settings.value : '',
          oPlanSettings: typeof this.$store !== 'undefined' && typeof this.$store.getters.getPlanSettings !== 'undefined' ? this.$store.getters.getPlanSettings : {}
        }
    },
    props: ['settings'],
    computed: {
        wrapperClass: function wrapperClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': this.value.length,
                'disable': typeof this.oPlanSettings['toggle_'+this.settings.key] !== 'undefined' && this.oPlanSettings['toggle_'+this.settings.key] == 'disable'
            }
        }
    },
    watch: {
        settings: {
            handler: function(){
                if ( this.value !== this.settings.value ){
                    this.value = this.settings.value;
                }
            },
            deep: true
        }
    },
    methods: {
        isRequired: function isRequired(pattern){
            if ( pattern == 'yes' || pattern === 'true' || pattern === true ){
                return true;
            }

            return false;
        },
        changed: function changed(){
            this.settings.value = this.value;
            this.$emit('inputChanged', this.value, this.settings);
        }
    }
};

var DatePicker = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isRequired)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt wilcity_datepicker",attrs:{"type":"text"},domProps:{"value":(_vm.value)},on:{"change":_vm.updatedDatepicker,"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt wilcity_datepicker",attrs:{"type":"text"},domProps:{"value":(_vm.value)},on:{"change":_vm.updatedDatepicker,"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.settings.label))]),_c('span',{staticClass:"bg-color-primary"}),_vm._v(" "),_vm._m(0)]),_vm._v(" "),(_vm.settings.errMsg)?_c('span',{staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))]):_vm._e()])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_right__2qM90 pos-a-center-right"},[_c('span',{staticClass:"field_icon__1_sOi"},[_c('i',{staticClass:"la la-calendar-check-o"})])])}],
    data: function data(){
        return {
            value: typeof this.settings.value !== 'undefined' ? this.settings.value : ''
        }
    },
    props: {
        settings: {
            type: Object,
            value: '',
            label: '',
            isRequired: 'no'
        },
        wrapperClass: {
            type: String,
            default: 'field_module__1H6kT field_style2__2Znhe mb-15'
        }
    },
    computed:{
        parseWrapperClass: function parseWrapperClass(){
            return this.selected != null && this.selected.length ? this.wrapperClass + ' active' : this.wrapperClass;
        },
    },
    methods: {
        updatedDatepicker: function updatedDatepicker(){
            this.settings.value = this.value;
            this.$emit('datepickerChanged', this.value, this.settings);
        },
        datepicker: function datepicker(){
            var self = this;
            jQuery(this.$el).find('.wilcity_datepicker').each(function(){
                jQuery(this).datepicker({
                    dateFormat: 'mm/dd/yy',
                    onSelect: function(dateText){
                        self.value = dateText;
                        self.$emit('input', self.value);
                        self.updatedDatepicker();
                    }
                });
            });
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        this.$nextTick(function (){
            this$1.datepicker();
        });
    }
};

var WilokeRangeDate = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-6"},[_c('date-picker',{attrs:{"settings":{value: _vm.from, label: _vm.fromLabel},"wrapper-class":"field_module__1H6kT field_style2__2Znhe"},model:{value:(_vm.from),callback:function ($$v) {_vm.from=$$v;},expression:"from"}})],1),_vm._v(" "),_c('div',{staticClass:"col-md-6"},[_c('date-picker',{attrs:{"settings":{value: _vm.to, label: _vm.toLabel},"wrapper-class":"field_module__1H6kT field_style2__2Znhe"},model:{value:(_vm.to),callback:function ($$v) {_vm.to=$$v;},expression:"to"}})],1)])},staticRenderFns: [],
    data: function data(){
        return {
            from: typeof this.value.from !== 'undefined' ? this.value.from :'',
            to: typeof this.value.to !== 'undefined' ? this.value.to :''
        }
    },
    props: ['fromLabel', 'toLabel', 'value'],
    components:{
        DatePicker: DatePicker
    },
    watch: {
        from: 'onChangedRange',
        to: 'onChangedRange'
    },
    methods: {
        onChangedRange: function onChangedRange(){
            this.$emit('onChangedRange', {
                from: this.from,
                to: this.to
            }, {key: 'date_range'});
        }
    }
};

var WilokeMessage = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{directives:[{name:"show",rawName:"v-show",value:(!_vm.isHidden),expression:"!isHidden"}],class:_vm.wrapperClass},[_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.icon!=''),expression:"icon!=''"}],staticClass:"alert_icon__1bDKL"},[_c('i',{class:_vm.icon})]),_vm._v(" "),_c('div',{staticClass:"alert_content__1ntU3",domProps:{"innerHTML":_vm._s(_vm.msg)}}),(_vm.hasRemove=='yes')?_c('a',{staticClass:"alert_close__3PtGd",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.hideMsg($event);}}},[_c('i',{staticClass:"la la-times"})]):_vm._e()])},staticRenderFns: [],
    data: function data(){
        return {
            isHidden: false
        }
    },
    props: {
        msg: {
            type: String,
            default: ''
        },
        hasRemove: {
            type: String,
            default: 'no'
        },
        status: {
            type: String,
            default: ''
        },
        icon: {
            type: String,
            default: ''
        },
        additionalClass: {
            type: String,
            default: ''
        }
    },
    computed: {
        wrapperClass: function wrapperClass(){
            var cssClass = 'alert_module__Q4QZx';

            switch(this.status){
                case 'danger':
                    cssClass += ' alert_danger__2ajVf';
                    break;
                case 'success':
                    cssClass += ' alert_success__1nkos';
                    break;
                case 'warning':
                    cssClass += ' alert_warning__2IUiO';
                    break;
                case 'info':
                    cssClass += ' alert_info__2dwkg';
                    break;
                case 'dark':
                    cssClass += ' alert_dark__3ks';
                default:
                    cssClass += ' ';
                    break;
            }

            return cssClass + ' ' + this.additionalClass;
        }
    },
    methods: {
        hideMsg: function hideMsg(){
            event.preventDefault();
            this.isHidden = true;
        }
    }
};

var WpSearch = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.parseWrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.s),expression:"s"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text"},domProps:{"value":(_vm.s)},on:{"keyup":_vm.changedValue,"input":function($event){if($event.target.composing){ return; }_vm.s=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"}),_vm._v(" "),_vm._m(0)])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_right__2qM90 pos-a-center-right"},[_c('span',{staticClass:"field_icon__1_sOi"},[_c('i',{staticClass:"la la-search"})])])}],
    data: function data(){
        return {
            s: this.value,
            onSearchChanged: null
        }
    },
    props: {
        label: {
            default: '',
            type: String
        },
        value: {
            default: '',
            type: String
        },
        wrapperClass: {
            default: 'field_module__1H6kT field_style2__2Znhe mb-15 select-text',
            type: String
        }
    },
    computed: {
        parseWrapperClass: function parseWrapperClass(){
            if ( this.s.length ){
                return this.wrapperClass + ' active';
            }
            return this.wrapperClass;
        }
    },
    mounted: function mounted(){
        this.resetValue();
    },
    methods: {
        resetValue: function resetValue(){
            var this$1 = this;

            this.$parent.$on('resetEverything', function (){ return this$1.s = ''; });
        },
        changedValue: function changedValue(){
            var this$1 = this;

            if ( this.onSearchChanged !== null ){
                clearTimeout(this.onSearchChanged);
            }

            this.onSearchChanged = setTimeout(function (){
                this$1.$emit('changedValue', this$1.s, {key: 'wp_search'});
                clearTimeout(this$1.onSearchChanged);
            }, 1000);
        }
    }
};

var WilokeCheckboxThree = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"checkbox_module__1K5IS checkbox_full__jTSmg mb-15 js-checkbox"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.status),expression:"status"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox","true-value":"yes","false-value":"no"},domProps:{"checked":Array.isArray(_vm.status)?_vm._i(_vm.status,null)>-1:_vm._q(_vm.status,"yes")},on:{"change":[function($event){var $$a=_vm.status,$$el=$event.target,$$c=$$el.checked?("yes"):("no");if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.status=$$a.concat([$$v]));}else{$$i>-1&&(_vm.status=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.status=$$c;}},_vm.changed]}}),_vm._v(" "),_vm._m(0),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(_vm.settings.label)+" "),_c('span',{staticClass:"checkbox-border"})])])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return {
             originalVal: this.settings.value,
             status: this.settings.value
        }
    },
    props: {
        settings: {
            type: Object,
            default: {}
        },
        wrapperClass: ''
    },
    methods: {
        changed: function changed(){
            this.settings.value = this.status;
            this.$emit('checkboxChanged', this.status, this.settings);
        },
        resetValue: function resetValue(){
            var this$1 = this;

            this.$parent.$on('resetEverything', function (){
                this$1.status = this$1.originalVal;
            });
        }
    },
    mounted: function mounted(){
        this.resetValue();
    }
};

var WilokeSlider = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_module__1H6kT field_style2__2Znhe mb-15 js-field"},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('div',{staticClass:"js-slider-info",attrs:{"data-active":_vm.infoStatus}},[_c('span',{staticClass:"js-slider-info__number"},[_vm._v(_vm._s(_vm.value))]),_vm._v(" "),(_vm.settings.unit)?_c('span',{staticClass:"js-slider-info__unit"},[_vm._v(" "+_vm._s(_vm.settings.unit))]):_vm._e()]),_vm._v(" "),_c('div',{staticClass:"js-slider"}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.settings.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})])])},staticRenderFns: [],
    data: function data(){
        return {
            originalVal: this.settings.value,
            value: this.settings.value,
            $slider: null
        }
    },
    props: ['settings', 'wrapperClass'],
    mounted: function mounted(){
        this.slider();
        this.resetValue();
    },
    computed: {
        infoStatus: function infoStatus(){
           return this.value > 0 ? 'true' : '';
        }
    },
    methods: {
        resetValue: function resetValue(){
            var this$1 = this;

            this.$parent.$on('resetEverything', function (){
                this$1.value = this$1.originalVal;
                this$1.$slider.slider('value', this$1.originalVal);
            });
        },
        slider: function slider(){
            var this$1 = this;

            var self = jQuery(this.$el),
                info = self.find('.js-slider-info');
                this.$slider = self.find('.js-slider');
            this.$slider.slider({
                range: 'min',
                min: 0,
                max: this.settings.maximum,
                value: this.value,
                slide: function (event, ui) {
                    this$1.value = ui.value;
                    this$1.$slider.attr('data-slider-value', this$1.value);
                },
                stop: function (event, ui){
                    this$1.$emit('sliderChanged', ui.value);
                }
            });
        }
    }
};

var WilokeAutoComplete = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.parseWrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isRequired)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.place),expression:"place"}],staticClass:"field_field__3U_Rt",attrs:{"id":"wilcity-searchbox-field","type":"text","placeholder":_vm.placeholder},domProps:{"value":(_vm.place)},on:{"change":_vm.onChanged,"input":function($event){if($event.target.composing){ return; }_vm.place=$event.target.value;}}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.place),expression:"place"}],staticClass:"field_field__3U_Rt",attrs:{"id":"wilcity-searchbox-field","type":"text","placeholder":_vm.placeholder},domProps:{"value":(_vm.place)},on:{"change":_vm.onChanged,"input":function($event){if($event.target.composing){ return; }_vm.place=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"}),_vm._v(" "),(_vm.settings.askVisitorForLocation=='yes')?_c('div',{staticClass:"field_right__2qM90 pos-a-center-right"},[_vm._m(0)]):_vm._e()]),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.settings.errMsg!=''),expression:"settings.errMsg!=''"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"field_icon__1_sOi"},[_c('i',{staticClass:"la la-search"})])}],
        props: {
            settings: {
                type: Object,
                default: function (){
                    return {
                        value: '',
                        askVisitorForLocation: 'no',
                        isRequired: 'no',
                        placeholder: ''
                    }
                }
            },
            wrapperClass: {
                type: String,
                default: 'field_module__1H6kT field_style2__2Znhe mb-15'
            }
        },
        data: function data(){
            return {
                instSearchBox: {},
                instAutocomplete: {},
                oGeoCode: {},
                place: typeof this.settings.value != 'undefined' ? this.settings.value : '',
                placeholder: typeof this.settings.placeholder !== 'undefined' ? this.settings.placeholder : this.place,
                originalValue: this.settings.value
            }
        },
        computed: {
            parseWrapperClass: function parseWrapperClass(){
                if ( this.place.length ){
                    return this.wrapperClass + ' active';
                }

                return this.wrapperClass;
            }
        },
        methods: {
            lookingAddress: function lookingAddress(){
                var oPlaces = this.instSearchBox.getPlaces();

                if (oPlaces.length === 0) {
                    this.$emit('geocode-changed', '', this.settings);
					return false;
				}

                var oPlace = oPlaces.pop(),
                    aGeoCode = {
                        address: oPlace.formatted_address,
                        lat: oPlace.geometry.location.lat(),
                        lng: oPlace.geometry.location.lng()
                    };
                this.place = oPlace.formatted_address;
                this.$emit('geocode-changed', aGeoCode, this.settings);
            },
            initAutocomplete: function initAutocomplete(){
                var this$1 = this;

                new Promise(function (resolve, reject){
                    if ( typeof google !== 'undefined' ){
                        resolve('loaded');
                    }
                }).then(function (msg){
                    var target = document.getElementById('wilcity-searchbox-field');
                    if ( target ){
                        this$1.instSearchBox = new google.maps.places.SearchBox(target);
                        this$1.instSearchBox.addListener('places_changed', this$1.lookingAddress);
                    }
                });
            },
            resetValue: function resetValue(){
                var this$1 = this;

                this.$parent.$on('resetEverything', function (){
                    this$1.place = this$1.originalValue;
                });
            },
            onChanged: function onChanged(event){
                if ( event.target.value==='' ){
                    this.$emit('geocode-changed', '', this.settings);
                }
            }
        },
        mounted: function mounted(){
            this.resetValue();
            this.initAutocomplete();
        }
    };

var GooglePlace = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('wiloke-auto-complete',{attrs:{"settings":{placeholder: _vm.oField.label, askVisitorForLocation:'yes', value: _vm.value}},on:{"geocode-changed":_vm.onAddressChanged}}),_vm._v(" "),_c('wiloke-slider',{directives:[{name:"show",rawName:"v-show",value:(_vm.oAddress.address.length || _vm.defaultAddress.length),expression:"oAddress.address.length || defaultAddress.length"}],attrs:{"settings":{label: _vm.oField.radiusLabel, maximum: _vm.oField.maxRadius, value: _vm.oField.defaultRadius, unit: _vm.unit}},on:{"sliderChanged":_vm.radiusChanged}})],1)},staticRenderFns: [],
    data: function data(){
        return {
            oAddress: this.oField,
            defaultAddress: this.value,
            unit: typeof this.oField.unit !== 'undefined' ? this.oField.unit : 'km',
            radius: typeof this.oField.defaultRadius !== 'undefined' ? this.oField.defaultRadius : 0,
            originalAddress: this.value
        }
    },
    props: ['oField', 'value'],
    components:{
        WilokeAutoComplete: WilokeAutoComplete,
        WilokeSlider: WilokeSlider
    },
    methods: {
        resetValue: function resetValue(){
            var this$1 = this;

            this.$parent.$on('resetEverything', function (){
                this$1.oAddress.address = this$1.originalAddress;
                this$1.oAddress.defaultRadius = this$1.oField.defaultRadius;
                this$1.$emit('resetEverything', true);
            });
        },
        onAddressChanged: function onAddressChanged(oNewVal){
            if ( oNewVal == '' ){
                this.$set(this.oAddress, 'address', '');
                this.$set(this.oAddress, 'lat', '');
                this.$set(this.oAddress, 'lng', '');
                this.$emit('addressChanged', '');
            }else{
                this.$set(this.$data, 'oAddress', oNewVal);
                this.$emit('addressChanged', {
                    address: this.oAddress.address,
                    lat: oNewVal.lat,
                    lng: oNewVal.lng,
                    radius: this.radius,
                    unit: this.unit
                });
            }
            this.defaultAddress = '';
        },
        radiusChanged: function radiusChanged(newVal){
            this.radius = newVal;
            this.$emit('addressChanged', {
                address: this.oAddress.address,
                lat: this.oAddress.lat,
                lng: this.oAddress.lng,
                radius: this.radius,
                unit: this.unit
            });
            this.defaultAddress = '';
        }
    },
    mounted: function mounted(){
        this.resetValue();
    }
};

var WilokeHeading = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"promo-item_group__2ZJhC"},[(_vm.title!=='')?_c('h3',{staticClass:"promo-item_title__3hfHG"},[_vm._v(_vm._s(_vm.title))]):_vm._e(),_vm._v(" "),(_vm.desc!=='')?_c('p',{staticClass:"promo-item_description__2nc26"},[_vm._v(_vm._s(_vm.desc))]):_vm._e()])},staticRenderFns: [],
    props: {
        title: {
            type: String,
            value: ''
        },
        desc: {
            type: String,
            value: ''
        }
    }
};

var WilokeSelectTwo = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.parseWrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isMultiple=='yes')?_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.selected),expression:"selected"}],class:_vm.selectTwoClass,attrs:{"multiple":"multiple"},on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.selected=$event.target.multiple ? $$selectedVal : $$selectedVal[0];}}},_vm._l((_vm.aOptions),function(option){return _c('option',{class:_vm.printOptionClass(option),domProps:{"value":_vm.printOptionValue(option),"innerHTML":_vm._s(_vm.printOptionName(option))}})})):_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.selected),expression:"selected"}],class:_vm.selectTwoClass,on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.selected=$event.target.multiple ? $$selectedVal : $$selectedVal[0];}}},_vm._l((_vm.aOptions),function(option){return _c('option',{class:_vm.printOptionClass(option),domProps:{"value":_vm.printOptionValue(option),"innerHTML":_vm._s(_vm.printOptionName(option))}})})),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis",class:{required: _vm.settings.isRequired=='yes'}},[_vm._v(_vm._s(_vm.settings.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})]),_vm._v(" "),(_vm.settings.errMsg)?_c('span',{staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))]):_vm._e()])},staticRenderFns: [],
    data: function data(){
        return {
            isTax: null,
            optionClass: null,
            selected: null,
            $select2: null
        }
    },
    components: {
        WilokeHeading: WilokeHeading
    },
    props: {
        'cId': {
            type: String,
            default: ''
        },
        'settings':{
            type: Object,
            default: {}
        },
        wrapperClass: {
            type: String,
            default: ''
        }
    },
    computed:{
        parseWrapperClass: function parseWrapperClass(){
            var wrapperClass = this.wrapperClass;

            if ( !wrapperClass.length ){
                wrapperClass = 'field_module__1H6kT field_style2__2Znhe mb-15';
            }
            return this.selected != null && this.selected.length ? wrapperClass + ' active' : wrapperClass;
        },
        selectTwoClass: function selectTwoClass(){
            var createClass = 'wilcity-select-2';
            if ( this.settings.isAjax && this.settings.isAjax == 'yes' ){
                createClass += ' is-ajax';
            }

            return createClass;
        },
        aOptions: function aOptions(){

            if ( typeof this.$store !== 'undefined' && typeof this.$store.state.aNewBusinessHoursOptions !== 'undefined' && this.$store.state.aNewBusinessHoursOptions.aHours.length && this.$store.state.aNewBusinessHoursOptions.cId == this.cId ){
                return this.$store.state.aNewBusinessHoursOptions.aHours;
            }else{
                if ( this.settings.terms ){
                    return typeof this.settings.terms == 'string' ? JSON.parse(this.settings.terms) : this.settings.terms;
                }else{
                    return typeof this.settings.options == 'string' ? JSON.parse(this.settings.options) : this.settings.options;
                }
            }
        }
    },
    watch: {
        //'settings': {
            //handler: function(oNewValue){
                //jQuery(this.$el).find('.wilcity-select-2').select2().val(oNewValue.value).trigger('change.select2');
            //},
            //deep: true
        //}
    },
    methods:{
        resetValue: function resetValue(){
            var this$1 = this;

            this.$parent.$on('resetEverything', function (){
                this$1.selected = this$1.settings.isMultiple == 'yes' ? [] : '';
                jQuery(this$1.$el).find('.wilcity-select-2').val(this$1.selected).trigger('change');
            });
        },
        setDefault: function setDefault(){
            if ( this.settings.isMultiple === 'yes' ){
                this.selected = this.settings.value.length ? this.settings.value : [];
            }else{
                this.selected = !WilCityHelpers.isNull(this.settings.value) ? this.settings.value : '';
            }
        },
        updateValue: function updateValue(val){
            if ( typeof val == 'undefined' ){
                return false;
            }

            if ( val === null ){
                val = this.settings.isMultiple == 'yes' ? [] : '';
            }

            if ( this.settings.isMultiple == 'yes' ){
                this.selected = val;
                this.settings.value = this.selected;
                this.$emit('selectTwoChanged', this.settings.value, this.settings);
            }else{
                if ( this.selected == val ){
                    return false;
                }
                this.selected = val;
                this.settings.value = this.selected;
                this.$emit('selectTwoChanged', this.settings.value, this.settings);
            }
        },
        printOptionValue: function printOptionValue(option){
            return typeof option.value !== 'undefined' ? option.value : option;
        },
        printOptionName: function printOptionName(option){
            return typeof option.name !== 'undefined' ? option.name : option;
        },
        printOptionClass: function printOptionClass(option){
            return typeof option.parent!== 'undefined' && option.parent !== 0 ? 'has-parent-term' : '';
        },
        maximumSelectionLength: function maximumSelectionLength(){
            if ( this.settings.maximum ){
                return this.settings.maximum;
            }

            return 10000;
        },
        selectTwo: function selectTwo(){
            var this$1 = this;

            this.$select2 = jQuery(this.$el).find('.wilcity-select-2');
            if ( this.$select2.hasClass('is-ajax') ){
                var oArgs = {
                    action: this.settings.ajaxAction
                };

                if ( typeof this.settings.ajaxArgs !== 'undefined' ){
                    oArgs = Object.assign({}, oArgs, this.settings.ajaxArgs);
                }

                this.$select2.select2({
                    ajax:{
                        url: WILOKE_GLOBAL.ajaxurl,
                        data: function (params) {
                            return Object.assign({}, {search: params.term}, oArgs);
                        },
                        processResults: function (data, params) {
                            if ( !data.success ){
                                return false;
                            }else{
                                return data.data.msg;
                            }
                        },
                        cache: true
                    },
                    minimumInputLength: 1
                }).on('select2:open', (function (event){
                    jQuery(event.currentTarget).closest('.field_module__1H6kT').addClass('active');
                })).on('change', (function (event){
                    this$1.updateValue(jQuery(event.currentTarget).val());
                }));
            }else{
                this.$select2.select2({
                    templateResult: function(state){
                        if (!state.id) {
                            return state.text;
                        }

                        var treeItemClass = jQuery(state.element).hasClass('has-parent-term') ? 'has-parent-term' : '';

                        var $state = jQuery('<span class="'+treeItemClass+'">'+state.text+'</span>');
                        return $state;
                    },
                    templateSelection: function(repo){
                        return repo.text.replace('&amp;', '&');
                    },
                    allowClear: true,
                    placeholder: '',
                    maximumSelectionLength: this.maximumSelectionLength()
                }).on('change', (function (event){
                    var $select2 = jQuery(event.currentTarget);
                    $select2.closest('.field_module__1H6kT').addClass('active');
                    var val = $select2.val();
                    if (WilCityHelpers.isNull(val)){
                        $select2.closest('.field_module__1H6kT').removeClass('active');
                    }else{
                        $select2.closest('.field_module__1H6kT').find('.select2-selection__rendered').attr('style', '');
                    }
                    this$1.updateValue(val);
                }));
            }

            this.triggerDefault();
        },
        triggerDefault: function triggerDefault(){
            if (!WilCityHelpers.isNull(this.settings.value)){
                this.$select2.closest('.field_module__1H6kT').addClass('active');
            }
        },
        updateOptions: function updateOptions(){
            var this$1 = this;

            this.$parent.$on('onUpdateOptions', function (options){
                this$1.settings.options = options;
                this$1.triggerDefault();
            });
        }
    },
    mounted: function mounted(){
        this.selectTwo();
        this.resetValue();
        this.updateOptions();
    },
    beforeMount: function beforeMount(){
        this.setDefault();
    }
};

var WilokeCheckboxTwo = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_module__1H6kT field_style2__2Znhe mb-15 js-field",class:{error: _vm.isReachedMaximum}},[_c('div',{staticClass:"row"},[(_vm.settings.label)?_c('div',{staticClass:"col-md-12"},[_c('wiloke-heading',{attrs:{"title":_vm.settings.label,"desc":_vm.settings.desc}})],1):_vm._e(),_vm._v(" "),_vm._l((_vm.settings.options),function(oOption,index){return _c('div',{class:_vm.itemClass},[_c('div',{staticClass:"checkbox_module__1K5IS mb-20 js-checkbox"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[(_vm.isUnChecked(oOption.value) && _vm.isReachedMaximum)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.settings.value),expression:"settings.value"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox","disabled":"disabled"},domProps:{"value":oOption.value,"checked":Array.isArray(_vm.settings.value)?_vm._i(_vm.settings.value,oOption.value)>-1:(_vm.settings.value)},on:{"change":[function($event){var $$a=_vm.settings.value,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=oOption.value,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.settings.value=$$a.concat([$$v]));}else{$$i>-1&&(_vm.settings.value=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.$set(_vm.settings, "value", $$c);}},_vm.checkboxTwoChanged]}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.settings.value),expression:"settings.value"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox"},domProps:{"value":oOption.value,"checked":Array.isArray(_vm.settings.value)?_vm._i(_vm.settings.value,oOption.value)>-1:(_vm.settings.value)},on:{"change":[function($event){var $$a=_vm.settings.value,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=oOption.value,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.settings.value=$$a.concat([$$v]));}else{$$i>-1&&(_vm.settings.value=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.$set(_vm.settings, "value", $$c);}},_vm.changed]}}),_vm._v(" "),_vm._m(0,true),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(oOption.label)+" "),_c('span',{staticClass:"checkbox-border"})])])])])})],2),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.isReachedMaximum),expression:"isReachedMaximum"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.overMaximumTagsWarning))])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return {
            isReachedMaximum: false
        }
    },
    components: {
        WilokeHeading: WilokeHeading
    },
    props: ['settings'],
    computed: {
        itemClass: function itemClass(){
            return this.settings.itemClass !== 'undefined' ? this.settings.itemClass : 'col-md-6 col-lg-4';
        }
    },
    watch: {
        settings: {
            handler: function handler(oNewVal){
                if ( oNewVal.value.length >= oNewVal.maximum ){
                    this.isReachedMaximum = true;
                }else{
                    this.isReachedMaximum = false;
                }

                this.$emit('checkboxTwoChanged', this.settings.value, this.settings);
            },
            deep: true
        }
    },
    methods:{
        isUnChecked: function isUnChecked(val){
            if ( this.settings.value.length && this.isReachedMaximum ){
                return this.settings.value.indexOf(val) == -1;
            }
            return false;
        },
        changed: function changed(){
            this.$emit('checkboxTwoChanged', this.settings.value, this.settings);
        }
    },
    beforeMount: function beforeMount(){
        if ( this.settings.value === '' || (this.settings.value === null) ){
            this.settings.value = [];
        }
    },
    mounted: function mounted(){
    }
};

var WilokeTags = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('div',{staticClass:"checkbox_module__1K5IS checkbox_full__jTSmg checkbox_toggle__vd6vd mb-20 js-checkbox"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.isFilterByTag),expression:"isFilterByTag"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox","true-value":"yes","false-value":"no"},domProps:{"checked":Array.isArray(_vm.isFilterByTag)?_vm._i(_vm.isFilterByTag,null)>-1:_vm._q(_vm.isFilterByTag,"yes")},on:{"change":[function($event){var $$a=_vm.isFilterByTag,$$el=$event.target,$$c=$$el.checked?("yes"):("no");if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.isFilterByTag=$$a.concat([$$v]));}else{$$i>-1&&(_vm.isFilterByTag=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.isFilterByTag=$$c;}},_vm.tagChanged]}}),_vm._v(" "),_vm._m(0),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(_vm.oTranslation.filterByTags)+" "),_c('span',{staticClass:"checkbox-border"})])])]),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isFilterByTag=='yes'),expression:"isFilterByTag=='yes'"}],staticClass:"wil-visible wil-scroll-bar"},_vm._l((_vm.settings.options),function(oTag){return _c('div',[_c('div',{staticClass:"checkbox_module__1K5IS mb-20 js-checkbox"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.aTags),expression:"aTags"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox"},domProps:{"value":oTag.value,"checked":Array.isArray(_vm.aTags)?_vm._i(_vm.aTags,oTag.value)>-1:(_vm.aTags)},on:{"change":[function($event){var $$a=_vm.aTags,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=oTag.value,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.aTags=$$a.concat([$$v]));}else{$$i>-1&&(_vm.aTags=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.aTags=$$c;}},_vm.tagChanged]}}),_vm._v(" "),_vm._m(1,true),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(oTag.name)+" "),_c('span',{staticClass:"checkbox-border"})])])])])}))])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})])},function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return {
            aTags: [],
            isFilterByTag: this.isExpandTag,
            oTranslation: WILCITY_I18
        }
    },
    props: ['isExpandTag', 'settings'],
    components:{
        WilokeCheckboxTwo: WilokeCheckboxTwo
    },
    mounted: function mounted(){
        this.resetValue();
        this.scrollBar();
    },
    methods: {
        resetValue: function resetValue(){
            var this$1 = this;

            this.$parent.$on('resetEverything', function (){ return this$1.aTags = []; });
        },
        tagChanged: function tagChanged(){
            if ( this.isFilterByTag == 'no' ){
                this.$emit('onTagChanged', []);
            }else{
                this.$emit('onTagChanged', this.aTags);
            }
        },
        scrollBar: function scrollBar() {
            var this$1 = this;

            this.$nextTick( function (){
                jQuery(this$1.$el).find(".wil-scroll-bar").wrapInner('<div class="wil-scroll-container"></div>');
                var scrollBar = this$1.$el.querySelector(".wil-scroll-bar");
                    new PerfectScrollbar(scrollBar);
            });
        }
    }
};

var BlockLoading = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isLoading=='yes'),expression:"isLoading=='yes'"}],staticClass:"full-load"},[_c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"pill-loading_loader__3LOnT"})])])},staticRenderFns: [],
    computed: {
        wrapperClass: function wrapperClass(){
            return 'pill-loading_module__3LZ6v ' + this.position;
        }
    },
    props: ['position', 'isLoading']
};

var WilokeSearchForm = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass,staticStyle:{"min-height":"150px"}},[_c('div',{class:_vm.innerClass},[_c('wiloke-message',{directives:[{name:"show",rawName:"v-show",value:(_vm.errorMsg!==''),expression:"errorMsg!==''"}],attrs:{"msg":_vm.errorMsg,"icon":"la la-frown-o"}}),_vm._v(" "),_c('block-loading',{attrs:{"position":"pos-a-center","is-loading":_vm.isLoading}}),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.aSearchFields.length),expression:"aSearchFields.length"}],class:_vm.wrapperFieldsClass},_vm._l((_vm.aSearchFields),function(oField){return _c('div',{class:_vm.wrapperFieldClass(oField)},[(oField.type=='wp_search')?_c('wp-search',{attrs:{"label":oField.label,"value":_vm.searchKeyDefault},on:{"changedValue":_vm.onSearchChanged}}):_vm._e(),_vm._v(" "),(oField.type=='date_range')?_c('wiloke-range-date',{attrs:{"from-label":oField.fromLabel,"to-label":oField.toLabel,"value":_vm.defaultDateRange},on:{"onChangedRange":_vm.onChangedRange}}):(oField.type=='checkbox' && oField.key!=='listing_tag')?_c('wiloke-checkbox-three',{attrs:{"settings":{label: oField.label, value: 'no', key: oField.key}},on:{"checkboxChanged":_vm.checkboxChanged}}):(oField.type=='checkbox2' && oField.key=='listing_tag')?_c('wiloke-tags',{directives:[{name:"show",rawName:"v-show",value:(_vm.aTags.length),expression:"aTags.length"}],attrs:{"settings":{value: oField.key, key: oField.key, options: _vm.aTags},"is-expand-tag":_vm.mayTagOpen},on:{"onTagChanged":_vm.onTagChanged}}):(oField.type=='slider')?_c('wiloke-slider',{attrs:{"settings":{label: oField.label, value: oField.key, maximum: oField.maxRadius, value: oField.defaultRadius, unit: oField.unit}}}):(oField.type=='autocomplete')?_c('google-place',{attrs:{"o-field":_vm.googlePlaceField(oField),"value":_vm.address},on:{"addressChanged":_vm.addressChanged}}):(oField.type=='select2')?_c('wiloke-select-two',{attrs:{"settings":{isAjax: oField.isAjax, options:oField.options, label: oField.label, isMultiple: oField.isMultiple, value: _vm.selectTwoDefault(oField), key: oField.key, ajaxAction: oField.ajaxAction, ajaxArgs:{taxonomy: oField.key, postType: _vm.post_type, get: 'slug'}},"c-id":oField.key},on:{"selectTwoChanged":_vm.selectTwoChanged}}):_vm._e()],1)}))],1)])},staticRenderFns: [],
    data: function data(){
        return{
            aSearchFields: [],
            post_type: this.type,
            isLoading: 'yes',
            errorMsg: '',
            successMsg: '',
            xhrSearchField: null,
            oArgs: {
                oAddress: {},
                s: typeof this.s !== 'undefined' ? this.s : '',
                listing_cat: '',
                listing_tag: [],
                post_type: this.type,
                listing_location: ''
            },
            aFullTags: [],
            aTags: [],
            isFindingFullTags: false,
            oTagsCache: {},
            isSetDefault: false,
            isFocusHidden: false,
            xhrTag: null,
            xhrSearch: null,
            oCacheArgs: [],
            foundPosts: 0,
            oTaxonomies: {}
        }
    },
    props: ['type', 'postsPerPage', 's', 'latLng', 'address', 'isMap', 'formItemClass', 'isPopup', 'rawTaxonomies', 'rawDateRange', 'imageSize'],
    computed: {
        wrapperClass: function wrapperClass(){
            return this.isPopup=='no' ? 'content-box_module__333d9' : '';
        },
        innerClass: function innerClass(){
            return this.isPopup=='no' ? 'content-box_body__3tSRB': '';
        },
        searchKeyDefault: function searchKeyDefault(){
            return typeof this.s !== 'undefined' ? this.s : '';
        },
        wrapperFieldsClass: function wrapperFieldsClass(){
            if ( this.isMap == 'no' ){
                return '';
            }
            return 'row';
        },
        mayTagOpen: function mayTagOpen(){
           return this.isMap=='yes' ? 'no' : 'yes';
        },
        defaultDateRange: function defaultDateRange(){
            if ( typeof this.rawDateRange !== 'undefined' && this.rawDateRange.length ){

                return JSON.parse(this.rawDateRange);
            }

            return '';
        }
    },
    components:{
        BlockLoading: BlockLoading,
        WilokeMessage: WilokeMessage,
        WilokeInput: WilokeInput,
        WilokeCheckboxThree: WilokeCheckboxThree,
        WpSearch: WpSearch,
        WilokeSelectTwo: WilokeSelectTwo,
        GooglePlace: GooglePlace,
        WilokeSlider: WilokeSlider,
        WilokeTags: WilokeTags,
        WilokeRangeDate: WilokeRangeDate
    },
    watch: {
        'oArgs': {
            handler: function handler(oNewVal){
                this.updateState();
            },
            deep: true
        },
        post_type: 'updateState'
    },
    methods: {
        googlePlaceField: function googlePlaceField(oField){
            if ( !WilCityHelpers.isNull(this.latLng) ){
                var aParseLatLng = this.latLng.split(',');
                oField.lat = aParseLatLng[0];
                oField.lng = aParseLatLng[1];
            }

            if ( !WilCityHelpers.isNull(this.address) ){
                oField.address = this.address;
            }

            return oField;
        },
        buildArgs: function buildArgs(oArgs){
            var this$1 = this;

            var oBuildArgs = {};
            oArgs.forEach(function (oField, order){
                switch(oField.type){
                    case 'select2':
                        if ( this$1.oTaxonomies !== null && typeof this$1.oTaxonomies[oField.key] !== 'undefined' ){
                            if ( typeof oField.isMultiple == 'undefined' || oField.isMultiple!='yes' ){
                                oBuildArgs[oField.key] = this$1.oTaxonomies[oField.key];
                            }else{
                                oBuildArgs[oField.key] = this$1.oTaxonomies[oField.key].split(',');
                            }
                        }else{
                            if ( typeof oField.isMultiple !== 'undefined' && oField.isMultiple=='yes' ){
                                oBuildArgs[oField.key] = typeof oField.value !== 'undefined' ? oField.value : [];
                            }else{
                                oBuildArgs[oField.key] = typeof oField.value !== 'undefined' ? oField.value : '';
                            }
                        }
                        break;
                    case 'listing_tag':
                        if ( this$1.oTaxonomies !== null && typeof this$1.oTaxonomies['listing_tag'] !== 'undefined'  ){
                            oBuildArgs['listing_tag'] = this$1.oTaxonomies['listing_tag'].split(',');
                        }else{
                            oBuildArgs['listing_tag'] = [];
                        }
                        break;
                    case 'autocomplete':
                        var aParseLatLng = [];
                        if ( typeof this$1.latLng !== 'undefined' && this$1.latLng.length ){
                            aParseLatLng = this$1.latLng.split(',');
                        }else{
                            aParseLatLng = ['', ''];
                        }

                        oBuildArgs['oAddress'] = {
                            address: typeof this$1.address !== 'undefined' ? this$1.address: '',
                            lat: aParseLatLng[0],
                            lng: aParseLatLng[1]
                        };
                        break;
                    case 'date_range':
                        if ( !WilCityHelpers.isNull(this$1.rawDateRange) ){
                            var oParseDateRange = JSON.parse(this$1.rawDateRange);
                            oBuildArgs[oField.key] = {
                                from: oParseDateRange.from,
                                to: oParseDateRange.to
                            };
                        }
                        break;
                    default:
                        oBuildArgs[oField.key] = typeof oField.value !== 'undefined' ? oField.value : '';
                        break;
                }
            });

            oBuildArgs['post_type'] = this.post_type;
            oBuildArgs['s'] = this.s;
            this.oCacheArgs[this.post_type] = oBuildArgs;
            this.oArgs = oBuildArgs;
        },
        updateState: function updateState(){
            if ( typeof this.$store !== 'undefined' ){
                this.$store.commit('updateOSearchArgs', this.oArgs);
                this.$store.commit('updatePostType', this.post_type);
                this.$store.commit('updateSearchField', {
                    postType: this.post_type,
                    aFields: this.aSearchFields
                });
                this.$emit('fetch-listings', this.isReset);
                this.isReset = false;
            }
        },
        wrapperFieldClass: function wrapperFieldClass(oField){
            if ( this.isMap == 'no' ){
                return '';
            }

            if ( oField.key == 'listing_tag' ){
                return 'col-md-12 col-lg-12';
            }
            return this.formItemClass;
        },
        setDefault: function setDefault(){
            if ( typeof this.taxonomy !== 'undefined' && this.taxonomy.length ){
                this.oArgs[this.taxonomy] = this.term;
            }

            if ( typeof this.latLng !== 'undefined' && this.latLng.length ){
                this.oArgs.oAddress = {
                    latLng: this.latLng,
                    address: this.address
                };
            }
        },
        resetSearchForm: function resetSearchForm(){
            var this$1 = this;

            this.$parent.$on('resetSearchForm', function (){
                this$1.oArgs = {};
                this$1.$set(this$1.$data, 'oArgs', {});
                this$1.$emit('resetEverything', true);
                this$1.searchListings();
            });
        },
        selectTwoDefault: function selectTwoDefault(oField){
            if ( typeof this.oArgs[oField.key] !== 'undefined' ) {
                return this.oArgs[oField.key];
            }

            if ( typeof oField.isMultiple !== 'undefined' && oField.isMultiple == 'yes' ){
                return [];
            }

            return '';
        },
        setFullTags: function setFullTags(aFields){
            var this$1 = this;

            if ( !this.isFindingFullTags ){
                this.isFindingFullTags = true;
                aFields.forEach(function (oField) {
                    if ( typeof oField.key !== 'undefined' && oField.key == 'listing_tag' ){
                        this$1.aFullTags = Object.values(oField.options);
                        this$1.aTags     = this$1.aFullTags;
                        return true;
                    }
                });
            }
        },
        fetchIndividualTag: function fetchIndividualTag(termSlug){
            var this$1 = this;

            if ( typeof this.oTagsCache[termSlug] !== 'undefined' ){
                this.aTags = this.oTagsCache[termSlug];
                return true;
            }

            if ( termSlug == -1 ){
                this.aTags = this.aFullTags;
            }

            if ( this.xhrTag !== null && this.xhrTag.status !== 200 ){
                this.xhrTag.abort();
            }

            this.xhrTag = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_fetch_individual_cat_tags',
                    termSlug: termSlug
                },
                success: function (response) {
                    if ( response.success ){
                        this$1.aTags = response.data;
                        this$1.oTagsCache[termSlug] = response.data;
                    }else{
                        this$1.aTags = this$1.aFullTags;
                        this$1.oTagsCache[termSlug] = this$1.aFullTags;
                    }
                }
            });
        },
        getSearchFields: function getSearchFields(){
            var this$1 = this;

            if ( this.$store.getters.getSearchFields(this.post_type) ){
                this.aSearchFields = this.$store.getters.getSearchFields(this.post_type);
                this.buildArgs(this.aSearchFields);
                this.setFullTags(this.aSearchFields);
                return true;
            }

            if ( this.xhrSearchField !== null && this.xhrSearchField.status !== 200 ){
                this.xhrSearchField.abort();
            }

            this.isLoading = 'yes';

            this.xhrSearchField = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_get_search_fields',
                    postType: this.post_type
                },
                success: function (response) {
                    if ( response.success ){
                        this$1.aSearchFields = Object.values(response.data.fields);
                        this$1.setFullTags(this$1.aSearchFields);
                        this$1.buildArgs(this$1.aSearchFields);
                        this$1.updateState();
                    }
                    this$1.isLoading = 'no';
                }
            });
        },
        deleteMapBounds: function deleteMapBounds(){
            if ( typeof this.oArgs.aBounds !== 'undefined' ){
                delete this.oArgs.aBounds;
            }
        },
        onTagChanged: function onTagChanged(aTags){
            this.$set(this.oArgs, 'listing_tag', aTags);
            if ( !WilCityHelpers.isMobile() ){
                this.searchListings();
            }
        },
        onChangedRange: function onChangedRange(oRange){
            if ( oRange.from.length && oRange.to.length ){
                this.$set(this.oArgs, 'date_range', oRange);
                if ( !WilCityHelpers.isMobile() ){
                    this.searchListings();
                }
            }
        },
        onSearchChanged: function onSearchChanged(newVal, oSettings){
            this.$set(this.oArgs, 's', newVal);
            if ( !WilCityHelpers.isMobile() ){
                this.searchListings();
            }
        },
        checkboxChanged: function checkboxChanged(newVal, oSettings){
            this.$set(this.oArgs, oSettings.key, newVal);
            if ( !WilCityHelpers.isMobile() ){
                this.searchListings();
            }
        },
        addressChanged: function addressChanged(oNewVal){
            this.oArgs.oAddress = oNewVal;
            this.deleteMapBounds();
            if ( !WilCityHelpers.isMobile() ){
                this.searchListings();
            }
        },
        selectTwoChanged: function selectTwoChanged(newVal, oSettings){
            if ( oSettings.key == 'listing_cat' ){
                this.fetchIndividualTag(newVal);
            }else if ( oSettings.key == 'listing_location' ){
                this.deleteMapBounds();
            }else if (oSettings.key == 'post_type'){
                this.post_type = newVal;
                this.getSearchFields();
            }
            this.$set(this.oArgs, oSettings.key, newVal);

            if ( !WilCityHelpers.isMobile() ){
                this.searchListings();
            }
        },
        maybeDefault: function maybeDefault(fieldKey){
            if ( typeof this.oQuery[fieldKey] !== 'undefined' ){
                return this.oQuery[fieldKey];
            }
            return '';
        },
        searchListings: function searchListings(){
            var this$1 = this;

            if ( this.isMap == 'yes' ){
                return false;
            }

            this.$emit('searching', false);
            if ( this.xhrSearch !== null && this.xhrSearch.status !== 200 ){
                this.xhrSearch.abort();
            }

            this.$emit('searching', true);

            var $pagination = jQuery('#wilcity-search-pagination');
            $pagination.data('oArgs', this.oArgs);

            this.xhrSearch = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    postType: this.post_type,
                    oArgs: this.$store.getters.getSearchArgs,
                    action: 'wilcity_search_listings',
                    postsPerPage: this.postsPerPage
                },
                success: function (response) {
                    if ( response.success ){
                        document.getElementById('wilcity-search-results').innerHTML = response.data.msg;
                        var $grid = jQuery('#wilcity-search-results');
                        $grid.find('.wilcity-preview-gallery').each(function () {
                            jQuery(this).wilcityMagnificGalleryPopup();
                        });
                        $grid.find('.wilcity-js-favorite').each(function () {
                            jQuery(this).wilcityFavoriteStatistic();
                        });
                        jQuery('body, html').stop().animate({scrollTop:0}, 500, 'swing');
                    }else{
                        document.getElementById('wilcity-search-results').innerHTML = response.data.msg;
                    }

                    this$1.$parent.$emit('foundPosts', response.data.maxPosts);
                    $pagination.trigger('resetPagination', [1, response.data.maxPosts]);
                    this$1.$emit('searching', false);
                }
            });
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        this.oTaxonomies = this.rawTaxonomies.length ? JSON.parse(this.rawTaxonomies) : {};
        this.resetSearchForm();
        this.getSearchFields();
        this.setDefault();
        jQuery('#wilcity-search-pagination').data('oArgs', this.oArgs);

        if ( this.taxonomy == 'listing_cat' ){
            this.fetchIndividualTag(this.term);
        }

        this.$parent.$on('onSearchListings', function (){
            this$1.searchListings();
        });
    }
};

WILOKE_GLOBAL.vmNoMap = new Vue({
	el: '#wilcity-no-map',
	store: WILCITY_VUEX,
	components: {
		WilokeSearchForm: WilokeSearchForm
	},
	data: function data(){
		return {
			foundPosts: 0,
			isFocusHidden: false,
			oTranslation: WILCITY_I18
		}
	},
	mounted: function mounted(){
		this.listenLoading();
		this.updateFoundPosts();
	},
	methods:{
		toggleSearchFormPopup: function toggleSearchFormPopup(){
			this.$store.commit('updatePopupStatus', {
				id: 'wilcity-search-form-popup',
				status: this.$store.getters.getPopupStatus('wilcity-search-form-popup') === 'open' ? 'close' : 'open'
			});
		},
		togglePreloader: function togglePreloader(status){
			if ( status ){
				jQuery(this.$el).find('.full-load').removeClass('focus-hidden');
			}else{
				jQuery(this.$el).find('.full-load').addClass('focus-hidden');
			}
		},
		updateFoundPosts: function updateFoundPosts(){
			var this$1 = this;

			this.foundPosts = jQuery('#wilcity-search-pagination').data('totals');
			this.$on('foundPosts', function (foundPosts){ return this$1.foundPosts=foundPosts; });
		},
		resetSearchForm: function resetSearchForm(){
			jQuery('#wilcity-search-pagination').data('page', 1);
			this.$emit('resetSearchForm', true);
		},
		switchStyle: function switchStyle(style){
			var $target = jQuery('#wilcity-search-results').find('.js-listing-module');

			if ( style === 'grid' ){
				$target.removeClass('js-listing-list');
				$target.parent().css({width: ''});
			}else{
				$target.addClass('js-listing-list');
				$target.parent().css({width: '100%'});
			}

			jQuery('#wilcity-search-pagination').data('style', style);
		},
		searching: function searching(status){
			var $preloader = jQuery(this.$el).find('.full-load');
			if ( status ){
				$preloader.removeClass('focus-hidden');
			}else{
				$preloader.addClass('focus-hidden');
			}
		},
		listenLoading: function listenLoading(){
			var $preloader = jQuery(this.$el).find('.full-load'),
				$body = jQuery('body');
			$body.on('blockLoading', function (event, gridID){
				if ( gridID === 'wilcity-search-results' ){
					$preloader.removeClass('focus-hidden');
				}
			});

			$body.on('paginationLoaded', function (event, response, gridID){
				if ( gridID === 'wilcity-search-results' ){
					jQuery('body, html').stop().animate({scrollTop:0}, 500, 'swing');
					$preloader.addClass('focus-hidden');
				}
			});
		}
	},
	computed: {
		isMobile: function isMobile(){
			return WilCityHelpers.isMobile();
		},
		loadingMakeSearchFormLookBetter: function loadingMakeSearchFormLookBetter(){
			return 'focus-hidden';
		},
		additionalPreloaderClass: function additionalPreloaderClass(){
			return 'focus-hidden';
		},
		searchResultAdditionalClass: function searchResultAdditionalClass(){
			return 'focus-visible-showing';
		},
		resultText: function resultText(){
			if ( this.foundPosts == 0 || this.foundPosts == 1 ){
				return this.oTranslation.oneResult;
			}else if ( this.foundPosts == 2 ){
				return this.oTranslation.twoResults;
			}else{
				return this.oTranslation.moreThanTwoResults;
			}
		}
	}
});

}());
