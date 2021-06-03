/* eslint-disable react/jsx-no-bind */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable camelcase */
/* eslint-disable no-restricted-syntax */
/* eslint-disable react/no-access-state-in-setstate */
/* eslint-disable react/prop-types */
/* eslint-disable no-plusplus */
/* eslint-disable react/destructuring-assignment */

import React, { Component } from 'react';

import { connect } from 'react-redux';
import { batchActions } from 'redux-batched-actions';

import {
  TopFilters,
  Wrapper,
  Button,
  ExtensionsHolder,
  ExtensionDetailsPopup,
  ExtensionDeletePopup,
} from '@centreon/ui';

import Hook from '../../../../components/Hook';
import axios from '../../../../axios';
import { fetchNavigationData } from '../../../../redux/actions/navigationActions';
import { fetchExternalComponents } from '../../../../redux/actions/externalComponentsActions';

class ExtensionsRoute extends Component {
  state = {
    deleteToggled: false,
    deletingEntity: false,
    extensionDetails: false,
    extensions: {
      module: { entities: [] },
      widget: { entities: [] },
    },
    extensionsInstallingStatus: {},
    extensionsUpdatingStatus: {},
    installed: false,
    modalDetailsActive: false,
    modalDetailsLoading: false,
    modalDetailsType: 'module',
    modulesActive: false,
    not_installed: false,
    search: '',
    updated: false,
    widgetsActive: false,
  };

  componentDidMount = () => {
    this.getData();
  };

  onChange = (value, key) => {
    const { filters } = this.state;
    const additionalValues = {};
    if (typeof this.state[key] !== 'undefined') {
      additionalValues[key] = value;
    }
    this.setState(
      {
        ...additionalValues,
        filters: {
          ...filters,
          [key]: value,
        },
      },
      this.getData,
    );
  };

  clearFilters = () => {
    this.setState(
      {
        installed: false,
        modulesActive: false,
        not_installed: false,
        nothingShown: false,
        search: '',
        updated: false,
        widgetsActive: false,
      },
      this.getData,
    );
  };

  getEntitiesByKeyAndVersionParam = (param, equals, key, callback) => {
    const { extensions } = this.state;
    const resArray = [];
    if (extensions) {
      const { status, result } = extensions;
      if (status) {
        for (let i = 0; i < result[key].entities.length; i++) {
          const entity = result[key].entities[i];
          if (entity.version[param] === equals) {
            resArray.push({
              id: entity.id,
              type: key,
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
        'module',
        (moduleIds) => {
          this.getEntitiesByKeyAndVersionParam(
            param,
            equals,
            'widget',
            (widgetIds) => {
              if (callback) {
                callback([...moduleIds, ...widgetIds]);
              }
            },
          );
        },
      );
    } else if (modulesActive) {
      this.getEntitiesByKeyAndVersionParam(
        param,
        equals,
        'module',
        (moduleIds) => {
          if (callback) {
            callback([...moduleIds]);
          }
        },
      );
    } else if (widgetsActive) {
      // inverted because of inverse logic for switches on/off false/true
      this.getEntitiesByKeyAndVersionParam(
        param,
        equals,
        'widget',
        (widgetIds) => {
          if (callback) {
            callback([...widgetIds]);
          }
        },
      );
    }
  };

  runActionOnAllEntities = (entityVersionType, equals, statusesKey) => {
    this.getAllEntitiesByVersionParam(entityVersionType, equals, (ids) => {
      this.setStatusesByIds(ids, statusesKey, () => {
        if (entityVersionType === 'outdated') {
          this.updateOneByOne(ids);
        } else if (entityVersionType === 'installed') {
          this.installOneByOne(ids);
        }
      });
    });
  };

  // reload menu entries on extensions actions (install/update/delete)
  reloadNavigation = () => {
    const { reloadNavigation } = this.props;
    reloadNavigation();
  };

  // reload external hooks and pages on extensions actions (install/update/delete)
  reloadExternalComponents = () => {
    const { reloadExternalComponents } = this.props;
    reloadExternalComponents();
  };

  setStatusesByIds = (ids, statusesKey, callback) => {
    let statuses = this.state[statusesKey];
    for (const { id } of ids) {
      statuses = {
        ...statuses,
        [id]: true,
      };
    }
    this.setState(
      {
        [statusesKey]: statuses,
      },
      callback,
    );
  };

  updateOneByOne = (ids) => {
    if (ids.length > 0) {
      const updatingEntity = ids.shift();
      this.updateById(updatingEntity.id, updatingEntity.type, () => {
        this.updateOneByOne(ids);
      });
    }
  };

  installOneByOne = (ids) => {
    if (ids.length > 0) {
      const installingEntity = ids.shift();
      this.installById(installingEntity.id, installingEntity.type, () => {
        this.installOneByOne(ids);
      });
    }
  };

  setStatusByKey = (key, id, callback) => {
    this.setState(
      {
        [key]: {
          ...this.state[key],
          [id]: false,
        },
      },
      () => {
        if (callback && typeof callback === 'function') {
          callback();
        }
      },
    );
  };

  // install/remove extension
  runAction = (loadingKey, action, id, type, callback) => {
    this.setStatusesByIds([{ id }], loadingKey, () => {
      axios(
        `internal.php?object=centreon_module&action=${action}&id=${id}&type=${type}`,
      )
        .post()
        .then(() => {
          this.getData(() => {
            this.setStatusByKey(loadingKey, id, callback);
            this.reloadNavigation();
          });
        })
        .catch((err) => {
          this.getData(() => {
            this.setStatusByKey(loadingKey, id, callback);
            this.reloadNavigation();
          });
          throw err;
        });
    });
  };

  installById = (id, type, callback) => {
    const { modalDetailsActive } = this.state;
    if (modalDetailsActive) {
      this.setState({
        modalDetailsLoading: true,
      });
      this.runAction('extensionsInstallingStatus', 'install', id, type, () => {
        this.getExtensionDetails(id, type);
      });
    } else {
      this.runAction(
        'extensionsInstallingStatus',
        'install',
        id,
        type,
        callback,
      );
    }
  };

  updateById = (id, type, callback) => {
    const { modalDetailsActive } = this.state;
    if (modalDetailsActive) {
      this.setState({
        modalDetailsLoading: true,
      });
      this.runAction('extensionsUpdatingStatus', 'update', id, type, () => {
        this.getExtensionDetails(id, type);
      });
    } else {
      this.runAction('extensionsUpdatingStatus', 'update', id, type, callback);
    }
  };

  deleteById = (id, type) => {
    const { modalDetailsActive } = this.state;
    this.setState(
      {
        deleteToggled: false,
        deletingEntity: false,
        modalDetailsLoading: modalDetailsActive,
      },
      () => {
        axios('internal.php?object=centreon_module&action=remove')
          .delete('', {
            params: {
              id,
              type,
            },
          })
          .then(() => {
            this.getData();
            this.reloadNavigation();
            if (modalDetailsActive) {
              this.getExtensionDetails(id, type);
            }
          });
      },
    );
  };

  toggleDeleteModal = (entity, type) => {
    const { deleteToggled } = this.state;
    this.setState({
      deleteToggled: !deleteToggled,
      deletingEntity: entity ? { ...entity, type } : false,
    });
  };

  getParsedGETParamsForExtensions = (callback) => {
    const { installed, not_installed, updated, search } = this.state;
    let params = '';
    const nothingShown = false;
    if (search) {
      params += `&search=${search}`;
    }
    if (installed && not_installed && updated) {
      callback(params, nothingShown);
    } else if (!installed && !not_installed && !updated) {
      callback(params, nothingShown);
    } else {
      if (updated) {
        params += '&updated=false';
      }
      if (!installed && not_installed) {
        params += '&installed=false';
      } else if (installed && !not_installed) {
        params += '&installed=true';
      }
      callback(params, nothingShown);
    }
  };

  getData = (callback) => {
    this.getParsedGETParamsForExtensions((params, nothingShown) => {
      this.setState({
        nothingShown,
      });
      if (!nothingShown) {
        axios(`internal.php?object=centreon_module&action=list${params}`)
          .get()
          .then(({ data }) => {
            this.setState(
              {
                extensions: data,
              },
              () => {
                if (callback && typeof callback === 'function') {
                  callback();
                }
              },
            );
          });
      }
    });
  };

  hideExtensionDetails = () => {
    this.setState({
      modalDetailsActive: false,
      modalDetailsLoading: false,
    });
  };

  activateExtensionsDetails = (id, type) => {
    this.setState(
      {
        modalDetailsActive: true,
        modalDetailsLoading: true,
        modalDetailsType: type,
      },
      () => {
        this.getExtensionDetails(id, type);
      },
    );
  };

  getExtensionDetails = (id, type) => {
    axios(
      `internal.php?object=centreon_module&action=details&type=${type}&id=${id}`,
    )
      .get()
      .then(({ data }) => {
        const { result } = data;
        if (result.images) {
          result.images = result.images.map((image) => {
            return `./${image}`;
          });
        }
        this.setState({
          extensionDetails: result,
          modalDetailsLoading: false,
        });
      });
  };

  getEntityByIdAndType = (id, type) => {
    const { extensions } = this.state;

    if (type === 'module') {
      return extensions.result.module.entities.find(
        (entity) => entity.id === id,
      );
    }
    return extensions.result.widget.entities.find((entity) => entity.id === id);
  };

  toggleDeleteModalByIdAndType = (id, type) => {
    const entity = this.getEntityByIdAndType(id, type);

    this.toggleDeleteModal(entity, type);
  };

  render() {
    const {
      extensions,
      modulesActive,
      deleteToggled,
      widgetsActive,
      not_installed,
      installed,
      updated,
      search,
      nothingShown,
      modalDetailsActive,
      modalDetailsLoading,
      modalDetailsType,
      extensionsUpdatingStatus,
      extensionsInstallingStatus,
      deletingEntity,
      extensionDetails,
    } = this.state;

    const hasNoSelection =
      ((installed && not_installed && updated) ||
        (!installed && !not_installed && !updated)) &&
      search.length === 0 &&
      ((modulesActive && widgetsActive) || (!modulesActive && !widgetsActive));

    return (
      <div>
        <TopFilters
          fullText={{
            filterKey: 'search',
            label: 'Search',
            value: search,
          }}
          switches={[
            [
              {
                customClass: 'container__col-md-4 container__col-xs-4',
                filterKey: 'not_installed',
                switchStatus: 'Not installed',
                switchTitle: 'Status',
                value: not_installed,
              },
              {
                customClass: 'container__col-md-4 container__col-xs-4',
                filterKey: 'installed',
                switchStatus: 'Installed',
                value: installed,
              },
              {
                customClass: 'container__col-md-4 container__col-xs-4',
                filterKey: 'updated',
                switchStatus: 'Outdated',
                value: updated,
              },
            ],
            [
              {
                customClass: 'container__col-sm-3 container__col-xs-4',
                filterKey: 'modulesActive',
                switchStatus: 'Module',
                switchTitle: 'Type',
                value: modulesActive,
              },
              {
                customClass: 'container__col-sm-3 container__col-xs-4',
                filterKey: 'widgetsActive',
                switchStatus: 'Widget',
                value: widgetsActive,
              },
              {
                button: true,
                buttonType: 'bordered',
                color: 'black',
                label: 'Clear Filters',
                onClick: this.clearFilters.bind(this),
              },
            ],
          ]}
          onChange={this.onChange.bind(this)}
        />
        <Wrapper>
          <Button
            buttonType="regular"
            color="orange"
            customClass="mr-2"
            label={`${hasNoSelection ? 'Update all' : 'Update selection'}`}
            style={{
              opacity: '1',
            }}
            onClick={this.runActionOnAllEntities.bind(
              this,
              'outdated',
              true,
              'extensionsUpdatingStatus',
            )}
          />
          <Button
            buttonType="regular"
            color="green"
            customClass="mr-2"
            label={`${hasNoSelection ? 'Install all' : 'Install selection'}`}
            onClick={this.runActionOnAllEntities.bind(
              this,
              'installed',
              false,
              'extensionsInstallingStatus',
            )}
          />
          <Hook path="/administration/extensions/manager" />
        </Wrapper>
        {extensions.result && !nothingShown ? (
          <>
            {extensions.result.module &&
            (modulesActive || (!modulesActive && !widgetsActive)) ? (
              <ExtensionsHolder
                entities={extensions.result.module.entities}
                installing={extensionsInstallingStatus}
                title="Modules"
                type="module"
                updating={extensionsUpdatingStatus}
                onCardClicked={this.activateExtensionsDetails}
                onDelete={this.toggleDeleteModal}
                onInstall={this.installById}
                onUpdate={this.updateById}
              />
            ) : null}
            {extensions.result.widget &&
            (widgetsActive || (!modulesActive && !widgetsActive)) ? (
              <ExtensionsHolder
                entities={extensions.result.widget.entities}
                hrColor="blue"
                hrTitleColor="blue"
                installing={extensionsInstallingStatus}
                title="Widgets"
                titleColor="blue"
                type="widget"
                updating={extensionsUpdatingStatus}
                onCardClicked={this.activateExtensionsDetails}
                onDelete={this.toggleDeleteModal}
                onInstall={this.installById}
                onUpdate={this.updateById}
              />
            ) : null}
          </>
        ) : null}

        {extensionDetails && modalDetailsActive ? (
          <ExtensionDetailsPopup
            loading={modalDetailsLoading}
            modalDetails={extensionDetails}
            type={modalDetailsType}
            onCloseClicked={this.hideExtensionDetails.bind(this)}
            onDeleteClicked={this.toggleDeleteModalByIdAndType}
            onInstallClicked={this.installById}
            onUpdateClicked={this.updateById}
          />
        ) : null}

        {deleteToggled ? (
          <ExtensionDeletePopup
            deletingEntity={deletingEntity}
            onCancel={this.toggleDeleteModal}
            onConfirm={this.deleteById}
          />
        ) : null}
      </div>
    );
  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    reloadNavigation: () => {
      // batch actions to avoid useless multiple rendering
      dispatch(
        batchActions([fetchNavigationData(), fetchExternalComponents()]),
      );
    },
  };
};

export default connect(null, mapDispatchToProps)(ExtensionsRoute);
