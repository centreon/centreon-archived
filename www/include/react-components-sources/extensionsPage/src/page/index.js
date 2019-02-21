import * as Centreon from "@centreon/react-components";
import React, { Component } from "react";
import { connect } from "react-redux";

class ExtensionsRoute extends Component {
  state = {
    widgetsActive: true,
    modulesActive: true,
    modalDetailsActive: false,
    modalDetailsLoading: false,
    not_installed: true,
    installed: true,
    updated: true,
    search: "",
    deleteToggled: false,
    deletingEntity: false,
    uploadToggled: false,
    extensionsUpdatingStatus: {},
    extensionsInstallingStatus: {},
    extensionDetails: false,
    uploadingProgress: 0,
    uploadingFinished: false,
    uploadingStarted: false,
    licenseUploadStatus: false
  };

  componentDidMount = () => {
    this.getData();
  };

  onChange = (value, key) => {
    const { filters } = this.state;
    let additionalValues = {};
    if (typeof this.state[key] != "undefined") {
      additionalValues[key] = value;
    }
    this.setState(
      {
        ...additionalValues,
        filters: {
          ...filters,
          [key]: value
        }
      },
      this.getData
    );
  };

  clearFilters = () => {
    this.setState(
      {
        widgetsActive: true,
        modulesActive: true,
        not_installed: true,
        installed: true,
        updated: true,
        nothingShown: false,
        search: ""
      },
      this.getData
    );
  };

  togglelicenseUpload = () => {
    const { uploadToggled } = this.state;
    this.setState({
      uploadingStarted: false,
      uploadingFinished: false,
      licenseUploadStatus: false,
      uploadToggled: !uploadToggled
    });
  };

  getEntitiesByKeyAndVersionParam = (param, equals, key, callback) => {
    const { remoteData } = this.props;
    const { extensions } = remoteData;
    let resArray = [];
    if (extensions) {
      const { status, result } = extensions;
      if (status) {
        for (let i = 0; i < result[key].entities.length; i++) {
          let entity = result[key].entities[i];
          if (entity.version[param] == equals) {
            resArray.push({
              id: entity.id,
              type: key
            });
          }
        }
      }
    }
    callback(resArray);
  };

  getAllEntitiesByVersionParam = (param, equals, callback) => {
    const { modulesActive, widgetsActive } = this.state;
    if (
      (!modulesActive && !widgetsActive) ||
      (modulesActive && widgetsActive)
    ) {
      this.getEntitiesByKeyAndVersionParam(
        param,
        equals,
        "module",
        moduleIds => {
          this.getEntitiesByKeyAndVersionParam(
            param,
            equals,
            "widget",
            widgetIds => {
              if (callback) {
                callback([...moduleIds, ...widgetIds]);
              }
            }
          );
        }
      );
    } else if (widgetsActive) {
      this.getEntitiesByKeyAndVersionParam(
        param,
        equals,
        "module",
        moduleIds => {
          if (callback) {
            callback([...moduleIds]);
          }
        }
      );
    } else if (modulesActive) {
      // inverted because of inverse logic for switchers on/off false/true
      this.getEntitiesByKeyAndVersionParam(
        param,
        equals,
        "widget",
        widgetIds => {
          if (callback) {
            callback([...widgetIds]);
          }
        }
      );
    }
  };

  runActionOnAllEntities = (entityVersionType, equals, statusesKey) => {
    this.getAllEntitiesByVersionParam(entityVersionType, equals, ids => {
      this.setStatusesByIds(ids, statusesKey, () => {
        if (entityVersionType === "outdated") {
          this.updateOneByOne(ids);
        } else if (entityVersionType === "installed") {
          this.installOneByOne(ids);
        }
      });
    });
  };

  reloadNavigation = () => {
    const { reloadNavigation } = this.props;
    reloadNavigation();
  };

  setStatusesByIds = (ids, statusesKey, callback) => {
    let statuses = this.state[statusesKey];
    for (let { id } of ids) {
      statuses = {
        ...statuses,
        [id]: true
      };
    }
    this.setState(
      {
        [statusesKey]: statuses
      },
      callback
    );
  };

  updateOneByOne = ids => {
    if (ids.length > 0) {
      const updatingEntity = ids.shift();
      this.updateById(updatingEntity.id, updatingEntity.type, () => {
        this.updateOneByOne(ids);
        this.reloadNavigation();
      });
    }
  };

  installOneByOne = ids => {
    if (ids.length > 0) {
      const installingEntity = ids.shift();
      this.installById(installingEntity.id, installingEntity.type, () => {
        this.installOneByOne(ids);
        this.reloadNavigation();
      });
    }
  };

  setStatusByKey = (key, id, callback) => {
    this.setState(
      {
        [key]: {
          ...this.state[key],
          [id]: false
        }
      },
      () => {
        if (callback && typeof callback === "function") {
          callback();
        }
      }
    );
  };

  runAction = (loadingKey, action, id, type, callback) => {
    this.setStatusesByIds([{ id }], loadingKey, () => {
      const { xhr } = this.props;
      xhr({
        requestType: "POST",
        url: `./api/internal.php?object=centreon_module&action=${action}&id=${id}&type=${type}`
      })
        .then(() => {
          this.getData(() => {
            this.setStatusByKey(loadingKey, id, callback);
          });
        })
        .catch(err => {
          this.getData(() => {
            this.setStatusByKey(loadingKey, id, callback);
          });
          throw err;
        });
    });
  };

  installById = (id, type, callback) => {
    const { modalDetailsActive } = this.state;
    if (modalDetailsActive) {
      this.setState({
        modalDetailsLoading: true
      });
      this.runAction("extensionsInstallingStatus", "install", id, type, () => {
        this.getExtensionDetails(id);
        this.reloadNavigation();
      });
    } else {
      this.runAction(
        "extensionsInstallingStatus",
        "install",
        id,
        type,
        callback ? callback : this.reloadNavigation
      );
    }
  };

  updateById = (id, type, callback) => {
    const { modalDetailsActive } = this.state;
    if (modalDetailsActive) {
      this.setState({
        modalDetailsLoading: true
      });
      this.runAction("extensionsUpdatingStatus", "update", id, type, () => {
        this.getExtensionDetails(id);
        this.reloadNavigation();
      });
    } else {
      this.runAction(
        "extensionsUpdatingStatus",
        "update",
        id,
        type,
        callback ? callback : this.reloadNavigation
      );
    }
  };

  deleteById = (id, type) => {
    const { xhr } = this.props;
    const { modalDetailsActive } = this.state;
    this.setState(
      {
        deleteToggled: false,
        deletingEntity: false,
        modalDetailsLoading: modalDetailsActive
      },
      () => {
        xhr({
          requestType: "DELETE",
          url: "./api/internal.php?object=centreon_module&action=remove",
          data: {
            params: {
              id,
              type
            }
          }
        })
          .then(() => {
            this.getData();
            this.reloadNavigation();
            if (modalDetailsActive) {
              this.getExtensionDetails(id);
            }
          })
          .catch(err => {
            throw err;
          });
      }
    );
  };

  toggleDeleteModal = (entity, type) => {
    const { deleteToggled } = this.state;
    this.setState({
      deletingEntity: entity ? { ...entity, type } : false,
      deleteToggled: !deleteToggled
    });
  };

  getParsedGETParamsForExtensions = callback => {
    const { installed, not_installed, updated, search } = this.state;
    let params = "";
    let nothingShown = false;
    if (search) {
      params += "&search=" + search;
    }
    if (installed && not_installed && updated) {
      callback(params, nothingShown);
    } else if (!installed && !not_installed && !updated) {
      callback(params, nothingShown);
    } else {
      if (!updated) {
        params += "&updated=false";
      }
      if (!installed && not_installed) {
        params += "&installed=true";
      } else if (installed && !not_installed) {
        params += "&installed=false";
      }
      callback(params, nothingShown);
    }
  };

  getData = callback => {
    const { xhr } = this.props;
    this.getParsedGETParamsForExtensions((params, nothingShown) => {
      this.setState({
        nothingShown
      });
      if (!nothingShown) {
        xhr({
          requestType: "GET",
          url: `./api/internal.php?object=centreon_module&action=list${params}`,
          propKey: "extensions"
        })
          .then(() => {
            if (callback && typeof callback === "function") {
              callback();
            }
          })
          .catch(err => {
            throw err;
          });
      }
    });
  };

  hideExtensionDetails = () => {
    this.setState({
      modalDetailsActive: false,
      modalDetailsLoading: false
    });
  };

  activateExtensionsDetails = id => {
    this.setState(
      {
        modalDetailsActive: true,
        modalDetailsLoading: true
      },
      () => {
        this.getExtensionDetails(id);
      }
    );
  };

  getExtensionDetails = id => {
    const { xhr } = this.props;
    xhr({
      requestType: "GET",
      url: `./api/internal.php?object=centreon_module&action=details&type=module&id=${id}`
    })
      .then(({ result }) => {
        this.setState({
          extensionDetails: result,
          modalDetailsLoading: false
        });
      })
      .catch(err => {
        throw err;
      });
  };

  versionClicked = id => {};

  resetUploadProgress = () => {
    const { xhr } = this.props;
    xhr({ requestType: "RESET_UPLOAD_PROGRESS" }).then(() => {
      this.setState({
        uploadingStarted: false
      });
    });
  };

  uploadFiles = files => {
    const { xhr } = this.props;
    this.setState(
      {
        uploadingStarted: true
      },
      () => {
        xhr({
          requestType: "UPLOAD",
          url: "./api/internal.php?object=centreon_license&action=file",
          files: files
        }).then(res => {
          this.setState(
            {
              licenseUploadStatus: res,
              uploadingFinished: true
            },
            () => {
              this.resetUploadProgress();
            }
          );
        });
      }
    );
  };

  render = () => {
    const { remoteData } = this.props;
    const { extensions, fileUploadProgress } = remoteData;
    const {
      modulesActive,
      deleteToggled,
      uploadToggled,
      widgetsActive,
      not_installed,
      installed,
      updated,
      search,
      nothingShown,
      modalDetailsActive,
      modalDetailsLoading,
      extensionsUpdatingStatus,
      extensionsInstallingStatus,
      deletingEntity,
      extensionDetails,
      licenseUploadStatus,
      uploadingFinished,
      uploadingStarted
    } = this.state;
    return (
      <div>
        <Centreon.TopFilters
          fullText={{
            label: "Search:",
            value: search,
            filterKey: "search"
          }}
          onChange={this.onChange.bind(this)}
          switchers={[
            [
              {
                customClass: "container__col-md-4 container__col-xs-4",
                switcherTitle: "Status:",
                switcherStatus: "Not installed",
                value: not_installed,
                filterKey: "not_installed"
              },
              {
                customClass: "container__col-md-4 container__col-xs-4",
                switcherStatus: "Installed",
                value: installed,
                filterKey: "installed"
              },
              {
                customClass: "container__col-md-4 container__col-xs-4",
                switcherStatus: "Outdated",
                value: updated,
                filterKey: "updated"
              }
            ],
            [
              {
                customClass: "container__col-sm-3 container__col-xs-4",
                switcherTitle: "Type:",
                switcherStatus: "Module",
                value: modulesActive,
                filterKey: "modulesActive"
              },
              {
                customClass: "container__col-sm-3 container__col-xs-4",
                switcherStatus: "Widget",
                value: widgetsActive,
                filterKey: "widgetsActive"
              },
              {
                button: true,
                label: "Clear Filters",
                color: "black",
                buttonType: "bordered",
                onClick: this.clearFilters.bind(this)
              }
            ]
          ]}
        />
        <Centreon.Wrapper>
          <Centreon.Button
            label={`${
              installed &&
              not_installed &&
              updated &&
              search.length === 0 &&
              ((modulesActive && widgetsActive) ||
                (!modulesActive && !widgetsActive))
                ? "Update all"
                : "Update selection"
            }`}
            buttonType="regular"
            customClass={`mr-2 ${false ? "opacity-1-3" : ""}`}
            color="orange"
            style={{
              opacity: false ? "0.33" : "1"
            }}
            onClick={this.runActionOnAllEntities.bind(
              this,
              "outdated",
              true,
              "extensionsUpdatingStatus"
            )}
          />
          <Centreon.Button
            label={`${
              installed &&
              not_installed &&
              updated &&
              search.length === 0 &&
              ((modulesActive && widgetsActive) ||
                (!modulesActive && !widgetsActive))
                ? "Install all"
                : "Install selection"
            }`}
            buttonType="regular"
            customClass={`mr-2 ${false ? "opacity-1-3" : ""}`}
            color="green"
            onClick={this.runActionOnAllEntities.bind(
              this,
              "installed",
              false,
              "extensionsInstallingStatus"
            )}
          />
          <Centreon.Button
            label={"Upload license"}
            buttonType="regular"
            color="blue"
            onClick={this.togglelicenseUpload.bind(this)}
          />
        </Centreon.Wrapper>
        {extensions && !nothingShown ? (
          <React.Fragment>
            {extensions.result.module &&
            (!modulesActive || (modulesActive && widgetsActive)) ? (
              <Centreon.ExtensionsHolder
                onCardClicked={this.activateExtensionsDetails}
                onDelete={this.toggleDeleteModal}
                onInstall={this.installById}
                onUpdate={this.updateById}
                titleIcon={"object"}
                title="Modules"
                type={"module"}
                updating={extensionsUpdatingStatus}
                installing={extensionsInstallingStatus}
                entities={extensions.result.module.entities}
              />
            ) : null}
            {extensions.result.widget &&
            (!widgetsActive || (modulesActive && widgetsActive)) ? (
              <Centreon.ExtensionsHolder
                onCardClicked={this.activateExtensionsDetails}
                onDelete={this.toggleDeleteModal}
                onInstall={this.installById}
                onUpdate={this.updateById}
                titleIcon={"puzzle"}
                title="Widgets"
                type={"widget"}
                updating={extensionsUpdatingStatus}
                installing={extensionsInstallingStatus}
                entities={extensions.result.widget.entities}
              />
            ) : null}
          </React.Fragment>
        ) : null}

        {extensionDetails && modalDetailsActive ? (
          <Centreon.ExtensionDetailsPopup
            loading={modalDetailsLoading}
            onCloseClicked={this.hideExtensionDetails.bind(this)}
            onVersionClicked={this.versionClicked}
            onInstallClicked={this.installById}
            onDeleteClicked={this.deleteById}
            onUpdateClicked={this.updateById}
            modalDetails={extensionDetails}
          />
        ) : null}

        {uploadToggled ? (
          <Centreon.FileUpload
            uploadingProgress={fileUploadProgress}
            uploadStatus={licenseUploadStatus}
            finished={uploadingFinished}
            uploading={uploadingStarted}
            onApply={this.uploadFiles}
            onClose={this.togglelicenseUpload.bind(this)}
          />
        ) : null}

        {deleteToggled ? (
          <Centreon.ExtensionDeletePopup
            deletingEntity={deletingEntity}
            onConfirm={this.deleteById}
            onCancel={this.toggleDeleteModal}
          />
        ) : null}
      </div>
    );
  };
}

const mapStateToProps = ({ remoteData }) => ({
  remoteData
});

const mapDispatchToProps = dispatch => ({
  xhr: data => {
    const { requestType } = data;
    return Centreon.Axios(data, dispatch, requestType);
  },
  reloadNavigation: () => {
    dispatch({
      type: "GET_NAVIGATION_DATA"
    });
  }
});

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(ExtensionsRoute);
