import * as Centreon from '@centreon/react-components';
import React, { Component } from "react";
import { connect } from 'react-redux';

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
    extensionDetails: false
  }

  componentDidMount = () => {
    this.getData();
  }

  onChange = (value, key) => {
    const { filters } = this.state;
    let additionalValues = {};
    if (typeof this.state[key] != 'undefined') {
      additionalValues[key] = value;
    }
    this.setState({
      ...additionalValues,
      filters: {
        ...filters,
        [key]: value
      }
    }, this.getData)
  }

  clearFilters = () => {
    this.setState({
      widgetsActive: true,
      modulesActive: true,
      not_installed: true,
      installed: true,
      updated: true,
      nothingShown: false,
      search: ""
    }, this.getData)
  }

  uploadLicence = () => {
    //TO DO: Pop up
  }

  toggleLicenceUpload = () => {
    const { uploadToggled } = this.state;
    this.setState({
      uploadToggled: !uploadToggled
    })
  }

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
            resArray.push(
              {
                id: entity.id,
                type: key
              }
            )
          }
        }
      }
    }
    callback(resArray);
  }


  getAllEntitiesByVersionParam = (param, equals, callback) => {
    this.getEntitiesByKeyAndVersionParam(param, equals, 'module',
      moduleIds => {
        this.getEntitiesByKeyAndVersionParam(param, equals, 'widget',
          widgetIds => {
            callback([...moduleIds, ...widgetIds]);
          })
      })
  }

  runActionOnAllEntities = (entityVersionType, equals, statusesKey) => {
    this.getAllEntitiesByVersionParam(
      entityVersionType,
      equals,
      (ids) => {
        this.setStatusesByIds(ids, statusesKey,
          () => {
            if(entityVersionType === 'outdated'){
              this.updateOneByOne(ids);
            }else if(entityVersionType === 'installed'){
              this.installOneByOne(ids);
            }
          }
        )
      }
    )
  }

  setStatusesByIds = (ids, statusesKey, callback) => {
    let statuses = this.state[statusesKey];
    for (let { id } of ids) {
      statuses = {
        ...statuses,
        [id]: true
      }
    }
    this.setState({
      [statusesKey]: statuses
    }, callback)
  }

  updateOneByOne = ids => {
    if (ids.length > 0) {
      const updatingEntity = ids.shift();
      this.updateById(updatingEntity.id, updatingEntity.type,
        () => {
          this.updateOneByOne(ids)
        }
      )
    }
  }

  installOneByOne = ids => {
    if (ids.length > 0) {
      const installingEntity = ids.shift();
      this.installById(installingEntity.id, installingEntity.type,
        () => {
          this.installOneByOne(ids)
        }
      )
    }
  }

  installById = (id, type, callback) => {
    this.setStatusesByIds([{ id }], 'extensionsInstallingStatus',
      () => {
        const { xhr } = this.props;
        xhr({
          requestType: 'POST',
          url: `./api/internal.php?object=centreon_module&action=install&id=${id}&type=${type}`,
        }).then(() => {
          this.getData(() => {
            this.setState({
              extensionsInstallingStatus: {
                ...this.state.extensionsInstallingStatus,
                [id]: false
              }
            }, () => {
              if (callback && typeof callback === 'function') {
                callback()
              }
            })
          });
        }).catch(
          err => {
            this.setState({
              extensionsInstallingStatus: {
                ...this.state.extensionsInstallingStatus,
                [id]: false
              }
            }, () => {
              if (callback && typeof callback === 'function') {
                callback()
              }
            })
            throw err;
          }
        );
      }
    )
  }

  updateById = (id, type, callback) => {
    this.setStatusesByIds([{ id }], 'extensionsUpdatingStatus',
      () => {
        const { xhr } = this.props;
        xhr({
          requestType: 'POST',
          url: `./api/internal.php?object=centreon_module&action=update&id=${id}&type=${type}`,
        }).then(() => {
          this.getData(() => {
            this.setState({
              extensionsUpdatingStatus: {
                ...this.state.extensionsUpdatingStatus,
                [id]: false
              }
            }, () => {
              if (callback && typeof callback === 'function') {
                callback()
              }
            })
          });
        }).catch(
          err => {
            this.setState({
              extensionsUpdatingStatus: {
                ...this.state.extensionsUpdatingStatus,
                [id]: false
              }
            }, () => {
              if (callback && typeof callback === 'function') {
                callback()
              }
            })
            throw err;
          }
        );
      }
    )
  }

  deleteById = (id, type) => {
    const { xhr } = this.props;
    this.setState({
      deleteToggled: false,
      deletingEntity: false,
    }, () => {
      xhr({
        requestType: 'DELETE',
        url: './api/internal.php?object=centreon_module&action=remove',
        data: {
          params: {
            id,
            type
          }
        }
      }).then(this.getData).catch(
        err => {
          throw err
        }
      )
    });
  }

  toggleDeleteModal = (entity, type) => {
    const { deleteToggled } = this.state;
    this.setState({
      deletingEntity: entity ? { ...entity, type } : false,
      deleteToggled: !deleteToggled
    })
  }

  getParsedGETParamsForExtensions = (callback) => {
    const { installed, not_installed, updated, search } = this.state;
    let params = '';
    let nothingShown = false;
    if (search) {
      params += '&search=' + search
    }
    if (installed && not_installed && updated) {
      callback(params, nothingShown);
    } else if (!installed && !not_installed && !updated) {
      callback(params, nothingShown);
    } else {
      if (!updated) {
        params += '&updated=false'
      }
      if (!installed && not_installed) {
        params += "&installed=true"
      } else if (installed && !not_installed) {
        params += "&installed=false"
      }
      callback(params, nothingShown);
    }
  }

  getData = (callback) => {
    const { xhr } = this.props;
    this.getParsedGETParamsForExtensions((params, nothingShown) => {
      this.setState({
        nothingShown
      })
      if (!nothingShown) {
        xhr({
          requestType: 'GET',
          url: `./api/internal.php?object=centreon_module&action=list${params}`,
          propKey: 'extensions'
        }).then(() => {
          if (callback && typeof callback === 'function') {
            callback();
          }
        }).catch((err) => {
          throw err;
        })
      }
    })
  }

  hideExtensionDetails = () => {
    this.setState({
      modalDetailsActive: false,
      modalDetailsLoading: false
    })
  }

  activateExtensionsDetails = (id) => {
    const { xhr } = this.props;
    this.setState({
      modalDetailsActive: true,
      modalDetailsLoading: true
    }, () => {
      xhr({
        requestType: 'GET',
        url: `./api/internal.php?object=centreon_module&action=details&type=module&id=${id}`
      }).then(({ result }) => {
        this.setState({
          extensionDetails: result,
          modalDetailsLoading: false
        })
      }).catch((err) => {
        throw err;
      })
    })

  }

  versionClicked = (id) => {

  }

  render = () => {

    const { remoteData } = this.props;
    const { extensions } = remoteData;
    const { modulesActive,
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
      extensionDetails } = this.state;
    return (
      <div>
        <Centreon.TopFilters
          fullText={{
            label: "Search:",
            value: search,
            filterKey: 'search'
          }}
          onChange={this.onChange.bind(this)}
          switchers={[
            [
              {
                customClass: "container__col-md-4 container__col-xs-4",
                switcherTitle: "Status:",
                switcherStatus: "Not installed",
                value: not_installed,
                filterKey: 'not_installed'
              },
              {
                customClass: "container__col-md-4 container__col-xs-4",
                switcherStatus: "Installed",
                value: installed,
                filterKey: 'installed'
              },
              {
                customClass: "container__col-md-4 container__col-xs-4",
                switcherStatus: "Outdated",
                value: updated,
                filterKey: 'updated'
              }
            ],
            [
              {
                customClass: "container__col-sm-3 container__col-xs-4",
                switcherTitle: "Type:",
                switcherStatus: "Module",
                value: modulesActive,
                filterKey: 'modulesActive'
              },
              {
                customClass: "container__col-sm-3 container__col-xs-4",
                switcherStatus: "Widget",
                value: widgetsActive,
                filterKey: 'widgetsActive'
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
            label={"Update all"}
            buttonType="regular"
            customClass="mr-2"
            color="orange"
            onClick={this.runActionOnAllEntities.bind(
              this,
              'outdated',
              true,
              'extensionsUpdatingStatus')}
          />
          <Centreon.Button
            label={"Install all"}
            buttonType="regular"
            customClass="mr-2"
            color="green"
            onClick={this.runActionOnAllEntities.bind(
              this,
              'installed',
              false,
              'extensionsInstallingStatus')}
          />
          <Centreon.Button
            label={"Upload licence"}
            buttonType="regular"
            color="blue"
            onClick={this.toggleLicenceUpload.bind(this)} />
        </Centreon.Wrapper>
        {
          extensions && !nothingShown ? (
            <React.Fragment>
              {
                extensions.result.module && (!modulesActive || (modulesActive && widgetsActive)) ? (
                  <Centreon.ExtensionsHolder
                    onCardClicked={this.activateExtensionsDetails}
                    onDelete={this.toggleDeleteModal}
                    onInstall={this.installById}
                    onUpdate={this.updateById}
                    titleIcon={"object"}
                    title="Modules"
                    type={'module'}
                    updating={extensionsUpdatingStatus}
                    installing={extensionsInstallingStatus}
                    entities={extensions.result.module.entities} />
                ) : null
              }
              {
                extensions.result.widget && (!widgetsActive || (modulesActive && widgetsActive)) ? (
                  <Centreon.ExtensionsHolder
                    onCardClicked={this.activateExtensionsDetails}
                    onDelete={this.toggleDeleteModal}
                    onInstall={this.installById}
                    onUpdate={this.updateById}
                    titleIcon={"puzzle"}
                    title="Widgets"
                    type={'widget'}
                    updating={extensionsUpdatingStatus}
                    installing={extensionsInstallingStatus}
                    entities={extensions.result.widget.entities} />
                ) : null
              }
            </React.Fragment>
          ) : null
        }

        {
          extensionDetails && modalDetailsActive && !modalDetailsLoading ? (
            <Centreon.ExtensionDetailsPopup
              onCloseClicked={this.hideExtensionDetails.bind(this)}
              onVersionClicked={this.versionClicked}
              modalDetails={extensionDetails}
            />
          ) : null
        }

        {
          uploadToggled ? 
          <Centreon.FileUpload
            onClose={this.toggleLicenceUpload.bind(this)}
          /> : 
          null
        }

        {
          deleteToggled ? <Centreon.ExtensionDeletePopup
            deletingEntity={deletingEntity}
            onConfirm={this.deleteById}
            onCancel={this.toggleDeleteModal}
          /> : null
        }

      </div>
    )
  }
}


const mapStateToProps = ({ remoteData }) => ({
  remoteData
})


const mapDispatchToProps = dispatch => ({
  xhr: (data) => {
    const { requestType } = data;
    return Centreon.Axios(data, dispatch, requestType)
  }
});


export default connect(mapStateToProps, mapDispatchToProps)(ExtensionsRoute);