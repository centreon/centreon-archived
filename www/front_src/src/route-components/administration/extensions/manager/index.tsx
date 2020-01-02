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
import Hook from '../../../../components/hook';

import axios from '../../../../axios';
import { fetchNavigationData } from '../../../../redux/actions/navigationActions';
import { fetchExternalComponents } from '../../../../redux/actions/externalComponentsActions';

interface Props {
  reloadNavigation: Function;
  reloadExternalComponents: Function;
}

interface State {
  extensions: object;
  widgetsActive: boolean;
  modulesActive: boolean;
  modalDetailsActive: boolean;
  modalDetailsLoading: boolean;
  modalDetailsType: string;
  not_installed: boolean;
  installed: boolean;
  updated: boolean;
  search: string;
  deleteToggled: boolean;
  deletingEntity: boolean;
  extensionsUpdatingStatus: object;
  extensionsInstallingStatus: object;
  extensionDetails: boolean;
  filters: object;
}

class ExtensionsRoute extends Component<Props, State> {
  public state = {
    extensions: {
      result: {
        module: { entities: [] },
        widget: { entities: [] },
      },
    },
    widgetsActive: true,
    modulesActive: true,
    modalDetailsActive: false,
    modalDetailsLoading: false,
    modalDetailsType: 'module',
    not_installed: true,
    installed: true,
    updated: true,
    search: '',
    deleteToggled: false,
    deletingEntity: false,
    extensionsUpdatingStatus: {},
    extensionsInstallingStatus: {},
    extensionDetails: false,
  };

  public componentDidMount = () => {
    this.getData();
  };

  private onChange = (value: string, key: string) => {
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

  private clearFilters = () => {
    this.setState(
      {
        widgetsActive: true,
        modulesActive: true,
        not_installed: true,
        installed: true,
        updated: true,
        nothingShown: false,
        search: '',
      },
      this.getData,
    );
  };

  private getEntitiesByKeyAndVersionParam = (
    param: string,
    equals: boolean,
    key: string,
    callback: Function,
  ) => {
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

  private getAllEntitiesByVersionParam = (
    param: string,
    equals: boolean,
    callback: Function,
  ) => {
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
    } else if (widgetsActive) {
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
    } else if (modulesActive) {
      // inverted because of inverse logic for switchers on/off false/true
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

  private runActionOnAllEntities = (
    entityVersionType,
    equals: boolean,
    statusesKey: string,
  ) => {
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
  private reloadNavigation = () => {
    const { reloadNavigation } = this.props;
    reloadNavigation();
  };

  // reload external hooks and pages on extensions actions (install/update/delete)
  private reloadExternalComponents = () => {
    const { reloadExternalComponents } = this.props;
    reloadExternalComponents();
  };

  private setStatusesByIds = (
    ids: Array,
    statusesKey: string,
    callback: Function,
  ) => {
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

  private updateOneByOne = (ids: Array) => {
    if (ids.length > 0) {
      const updatingEntity = ids.shift();
      this.updateById(updatingEntity.id, updatingEntity.type, () => {
        this.updateOneByOne(ids);
      });
    }
  };

  private installOneByOne = (ids: Array) => {
    if (ids.length > 0) {
      const installingEntity = ids.shift();
      this.installById(installingEntity.id, installingEntity.type, () => {
        this.installOneByOne(ids);
      });
    }
  };

  private setStatusByKey = (key: string, id: string, callback: Function) => {
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
  private runAction = (
    loadingKey: string,
    action: string,
    id: string,
    type: string,
    callback: Function,
  ) => {
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

  private installById = (id: string, type: string, callback: Function) => {
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

  private updateById = (id: string, type: string, callback: Function) => {
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

  private deleteById = (id: string, type: string) => {
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

  private toggleDeleteModal = (entity: object, type: string) => {
    const { deleteToggled } = this.state;
    this.setState({
      deletingEntity: entity ? { ...entity, type } : false,
      deleteToggled: !deleteToggled,
    });
  };

  private getParsedGETParamsForExtensions = (callback: Function) => {
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
      if (!updated) {
        params += '&updated=false';
      }
      if (!installed && not_installed) {
        params += '&installed=true';
      } else if (installed && !not_installed) {
        params += '&installed=false';
      }
      callback(params, nothingShown);
    }
  };

  private getData = (callback: Function) => {
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

  private hideExtensionDetails = () => {
    this.setState({
      modalDetailsActive: false,
      modalDetailsLoading: false,
    });
  };

  private activateExtensionsDetails = (id: string, type: string) => {
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

  private getExtensionDetails = (id: string, type: string) => {
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

  private versionClicked = () => {};

  public render() {
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

    return (
      <div>
        <TopFilters
          fullText={{
            label: 'Search',
            value: search,
            filterKey: 'search',
          }}
          onChange={this.onChange.bind(this)}
          switchers={[
            [
              {
                customClass: 'container__col-md-4 container__col-xs-4',
                switcherTitle: 'Status',
                switcherStatus: 'Not installed',
                value: not_installed,
                filterKey: 'not_installed',
              },
              {
                customClass: 'container__col-md-4 container__col-xs-4',
                switcherStatus: 'Installed',
                value: installed,
                filterKey: 'installed',
              },
              {
                customClass: 'container__col-md-4 container__col-xs-4',
                switcherStatus: 'Outdated',
                value: updated,
                filterKey: 'updated',
              },
            ],
            [
              {
                customClass: 'container__col-sm-3 container__col-xs-4',
                switcherTitle: 'Type',
                switcherStatus: 'Module',
                value: modulesActive,
                filterKey: 'modulesActive',
              },
              {
                customClass: 'container__col-sm-3 container__col-xs-4',
                switcherStatus: 'Widget',
                value: widgetsActive,
                filterKey: 'widgetsActive',
              },
              {
                button: true,
                label: 'Clear Filters',
                color: 'black',
                buttonType: 'bordered',
                onClick: this.clearFilters.bind(this),
              },
            ],
          ]}
        />
        <Wrapper>
          <Button
            label={`${
              installed &&
              not_installed &&
              updated &&
              search.length === 0 &&
              ((modulesActive && widgetsActive) ||
                (!modulesActive && !widgetsActive))
                ? 'Update all'
                : 'Update selection'
            }`}
            buttonType="regular"
            customClass="mr-2"
            color="orange"
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
            label={`${
              installed &&
              not_installed &&
              updated &&
              search.length === 0 &&
              ((modulesActive && widgetsActive) ||
                (!modulesActive && !widgetsActive))
                ? 'Install all'
                : 'Install selection'
            }`}
            buttonType="regular"
            customClass="mr-2"
            color="green"
            onClick={this.runActionOnAllEntities.bind(
              this,
              'installed',
              false,
              'extensionsInstallingStatus',
            )}
          />
          <Hook path="/administration/extensions/manager/button" />
        </Wrapper>
        {extensions && !nothingShown ? (
          <>
            {extensions.result.module &&
            (!modulesActive || (modulesActive && widgetsActive)) ? (
              <ExtensionsHolder
                onCardClicked={this.activateExtensionsDetails}
                onDelete={this.toggleDeleteModal}
                onInstall={this.installById}
                onUpdate={this.updateById}
                title="Modules"
                type="module"
                updating={extensionsUpdatingStatus}
                installing={extensionsInstallingStatus}
                entities={extensions.result.module.entities}
              />
            ) : null}
            {extensions.result.widget &&
            (!widgetsActive || (modulesActive && widgetsActive)) ? (
              <ExtensionsHolder
                onCardClicked={this.activateExtensionsDetails}
                onDelete={this.toggleDeleteModal}
                onInstall={this.installById}
                onUpdate={this.updateById}
                titleColor="blue"
                hrTitleColor="blue"
                hrColor="blue"
                title="Widgets"
                type="widget"
                updating={extensionsUpdatingStatus}
                installing={extensionsInstallingStatus}
                entities={extensions.result.widget.entities}
              />
            ) : null}
          </>
        ) : null}

        {extensionDetails && modalDetailsActive ? (
          <ExtensionDetailsPopup
            type={modalDetailsType}
            loading={modalDetailsLoading}
            onCloseClicked={this.hideExtensionDetails.bind(this)}
            onVersionClicked={this.versionClicked}
            onInstallClicked={this.installById}
            onDeleteClicked={this.deleteById}
            onUpdateClicked={this.updateById}
            modalDetails={extensionDetails}
          />
        ) : null}

        {deleteToggled ? (
          <ExtensionDeletePopup
            deletingEntity={deletingEntity}
            onConfirm={this.deleteById}
            onCancel={this.toggleDeleteModal}
          />
        ) : null}
      </div>
    );
  }
}

const mapDispatchToProps = (dispatch: Function) => {
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
