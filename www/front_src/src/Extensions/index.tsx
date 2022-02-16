import React, { useState, useEffect } from 'react';

import { find, propEq } from 'ramda';
import { compose } from 'redux';
import axios from 'axios';
import { useAtomValue } from 'jotai/utils';

import UpdateIcon from '@mui/icons-material/SystemUpdateAlt';
import InstallIcon from '@mui/icons-material/Add';
import { Button } from '@mui/material';

import {
  useRequest,
  getData,
  postData,
  deleteData,
  SelectEntry,
} from '@centreon/ui';

import Hook from '../components/Hook';
import useNavigation from '../Navigation/useNavigation';
import useExternalComponents from '../externalComponents/useExternalComponents';

import ExtensionsHolder from './ExtensionsHolder';
import ExtensionDetailsPopup from './ExtensionDetailsDialog';
import ExtensionDeletePopup from './ExtensionDeleteDialog';
import Wrapper from './Wrapper';
import Filter from './Filter';
import { Extensions, EntityType, ExtensionsStatus } from './models';
import buildEndPoint from './api/endpoint';
import { appliedFilterCriteriasAtom, searchAtom } from './Filter/filterAtoms';

interface EntityDetails {
  id: string;
  type: string;
}

interface EntityDeleting {
  description: string;
  id: string;
  type: string;
}

// eslint-disable-next-line react/prop-types
const ExtensionsManager = ({ reloadNavigation }): JSX.Element => {
  const [extensions, setExtension] = useState<Extensions>({
    result: {
      module: {
        entities: [],
      },
      widget: {
        entities: [],
      },
    },
    status: false,
  });

  const [modulesActive, setModulesActive] = useState(false);
  const [widgetsActive, setWidgetsActive] = useState(false);

  const [entityDetails, setEntityDetails] = useState<EntityDetails | null>(
    null,
  );

  const [entityDeleting, setEntityDeleting] = useState<EntityDeleting | null>(
    null,
  );

  const [extensionsInstallingStatus, setExtensionsInstallingStatus] =
    useState<ExtensionsStatus>({});

  const [extensionsUpdatingStatus, setExtensionsUpdatingStatus] =
    useState<ExtensionsStatus>({});

  // const [installed, setInstalled] = useState(false);
  // const [notInstalled, setNotInstalled] = useState(false);
  // const [updated, setUpdated] = useState(false);
  // const [outdated, setOutdated] = useState(false);

  const [confirmedDeletingEntityId, setConfirmedDeletingEntityId] = useState<
    string | boolean | null
  >(false);

  const { sendRequest: sendExtensionsValueRequests } = useRequest<Extensions>({
    request: getData,
  });

  const { sendRequest: sendUpdateOrInstallExtensionValueRequests } = useRequest(
    {
      request: postData,
    },
  );

  const getAppliedFilterCriteriasAtom = useAtomValue(
    appliedFilterCriteriasAtom,
  );

  const getSearchAtom = useAtomValue(searchAtom);

  useEffect(() => {
    const types = find(propEq('name', 'types'), getAppliedFilterCriteriasAtom);

    if (types && types.value) {
      const typesValues = types.value as Array<SelectEntry>;
      setModulesActive(!!find(propEq('id', 'MODULE'), typesValues));
      setWidgetsActive(!!find(propEq('id', 'WIDGET'), typesValues));
    }

    const statuses = find(
      propEq('name', 'statuses'),
      getAppliedFilterCriteriasAtom,
    );

    if (statuses && statuses.value) {
      const statusesValues = statuses.value as Array<SelectEntry>;

      // setInstalled(!!find(propEq('id', 'INSTALLED'), statusesValues));
      // setNotInstalled(!!find(propEq('id', 'NOTINSTALLED'), statusesValues));
      // setUpdated(!!find(propEq('id', 'UPDATED'), statusesValues));
      // setOutdated(!!find(propEq('id', 'OUTDATED'), statusesValues));
    }

    const extensionsValue = sendExtensionsValueRequests({
      endpoint: `./api/internal.php?object=centreon_module&action=list${getParsedGETParamsForExtensions()}`,
    }).then((values) => {
      setExtension(values);
    });
  }, [getAppliedFilterCriteriasAtom]);

  const getEntitiesByKeyAndVersionParam = (
    param,
    equals,
    key,
  ): Array<EntityType> => {
    const resArray: Array<EntityType> = [];
    if (extensions) {
      const { status, result } = extensions;
      if (status) {
        for (let i = 0; i < result[key].entities.length; i += 1) {
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

    return resArray;
  };

  const getAllEntitiesByVersionParam = (param, equals): Array<EntityType> => {
    if (
      (!modulesActive && !widgetsActive) ||
      (modulesActive && widgetsActive)
    ) {
      return [
        ...getEntitiesByKeyAndVersionParam(param, equals, 'module'),
        ...getEntitiesByKeyAndVersionParam(param, equals, 'widget'),
      ];
    }
    if (modulesActive) {
      return [...getEntitiesByKeyAndVersionParam(param, equals, 'module')];
    }
    // inverted because of inverse logic for switches on/off false/true

    return [...getEntitiesByKeyAndVersionParam(param, equals, 'widget')];
  };

  const getParsedGETParamsForExtensions = (): string => {
    let params = '';
    const nameEqualsSearch = propEq('name', 'search');
    const nameEqualsStatuses = propEq('name', 'statuses');

    // const searchValue = find(nameEqualsSearch, getAppliedFilterCriteriasAtom);
    const statuses = find(nameEqualsStatuses, getAppliedFilterCriteriasAtom);

    if (!statuses || !statuses.value) {
      return '';
    }

    const values = statuses.value as Array<SelectEntry>;

    const installed = find(propEq('id', 'INSTALLED'), values);
    const notInstalled = find(propEq('id', 'NOTINSTALLED'), values);
    const updated = find(propEq('id', 'UPDATED'), values);
    const outdated = find(propEq('id', 'OUTDATED'), values);

    if (!updated && outdated) {
      params += '&updated=false';
    } else if (updated && !outdated) {
      params += '&updated=true';
    }

    if (!installed && notInstalled) {
      params += '&installed=false';
    } else if (installed && !notInstalled) {
      params += '&installed=true';
    }

    console.log(`----------------${params}`);

    return params;
  };

  const updateAllEntities = (): void => {
    const entities = getAllEntitiesByVersionParam('outdated', true);
    if (entities.length <= 0 || !entities) {
      return;
    }
    entities.forEach((entity) => {
      updateById(entity.id, entity.type);
    });
  };

  const updateById = (id: string, type: string): void => {
    setExtensionsUpdatingStatusByIds(id, true);
    const updateExtensionValue = sendUpdateOrInstallExtensionValueRequests({
      endpoint: buildEndPoint({
        action: 'update',
        id,
        type,
      }),
    });
    const extensionsValue = sendExtensionsValueRequests({
      endpoint: `./api/internal.php?object=centreon_module&action=list${getParsedGETParamsForExtensions()}`,
    });

    Promise.all([updateExtensionValue, extensionsValue]).then((values) => {
      setExtension(values[1]);
      setExtensionsUpdatingStatusByIds(id, false);
      reloadNavigation();
    });
  };

  const installAllEntities = (): void => {
    const entities = getAllEntitiesByVersionParam('installed', false);
    if (entities.length <= 0 || !entities) {
      return;
    }
    entities.forEach((entity) => {
      installById(entity.id, entity.type);
    });
  };

  const installById = (id: string, type: string): void => {
    setExtensionsInstallingStatusByIds(id, true);

    const installingExtensionValue = sendUpdateOrInstallExtensionValueRequests({
      endpoint: buildEndPoint({
        action: 'install',
        id,
        type,
      }),
    });

    const extensionsValue = sendExtensionsValueRequests({
      endpoint: `./api/internal.php?object=centreon_module&action=list${getParsedGETParamsForExtensions()}`,
    });

    Promise.all([installingExtensionValue, extensionsValue]).then((values) => {
      setExtension(values[1]);
      setExtensionsInstallingStatusByIds(id, false);
      reloadNavigation();
    });
  };

  const setExtensionsUpdatingStatusByIds = (
    id: string,
    updating: boolean,
  ): void => {
    let statuses = extensionsInstallingStatus;
    statuses = {
      ...statuses,
      [id]: updating,
    };
    setExtensionsUpdatingStatus(statuses);
  };

  const setExtensionsInstallingStatusByIds = (
    id: string,
    installing: boolean,
  ): void => {
    let statuses = extensionsInstallingStatus;
    statuses = {
      ...statuses,
      [id]: installing,
    };
    setExtensionsInstallingStatus(statuses);
  };

  // Extension Listing ...
  const activateExtensionsDetails = (id, type): void => {
    setEntityDetails({
      id,
      type,
    });
  };

  const toggleDeleteModal = (id, type, description): void => {
    if (entityDeleting) {
      setEntityDeleting(null);

      return;
    }

    setEntityDeleting({
      description,
      id,
      type,
    });
  };

  // Extension popup

  const hideExtensionDetails = (): void => {
    setEntityDetails(null);
  };

  const deleteById = (id, type): void => {
    setConfirmedDeletingEntityId(id);

    axios
      .delete('./api/internal.php?object=centreon_module&action=remove', {
        params: {
          id,
          type,
        },
      })
      .then(() => {
        sendExtensionsValueRequests({
          endpoint: `./api/internal.php?object=centreon_module&action=list${getParsedGETParamsForExtensions()}`,
        }).then((values) => {
          setConfirmedDeletingEntityId(null);
          setExtension(values);
          setEntityDeleting(null);
          reloadNavigation();
        });
      });
  };

  return (
    <div>
      <Filter />
      <Wrapper>
        <Button
          color="primary"
          size="small"
          startIcon={<UpdateIcon />}
          variant="contained"
          onClick={updateAllEntities}
        >
          update all
        </Button>
        <Button
          color="primary"
          size="small"
          startIcon={<InstallIcon />}
          style={{ marginLeft: 8, marginRight: 8 }}
          variant="contained"
          onClick={installAllEntities}
        >
          install all
        </Button>
        <Hook path="/administration/extensions/manager" />
      </Wrapper>

      {extensions.result ? (
        <>
          {extensions.result.module &&
          (modulesActive || (!modulesActive && !widgetsActive)) ? (
            <ExtensionsHolder
              deletingEntityId={confirmedDeletingEntityId}
              entities={extensions.result.module.entities}
              installing={extensionsInstallingStatus}
              title="Modules"
              type="module"
              updating={extensionsUpdatingStatus}
              onCardClicked={activateExtensionsDetails}
              onDelete={toggleDeleteModal}
              onInstall={installById}
              onUpdate={updateById}
            />
          ) : null}
          {extensions.result.widget &&
          (widgetsActive || (!modulesActive && !widgetsActive)) ? (
            <ExtensionsHolder
              deletingEntityId={confirmedDeletingEntityId}
              entities={extensions.result.widget.entities}
              installing={extensionsInstallingStatus}
              title="Widgets"
              type="widget"
              updating={extensionsUpdatingStatus}
              onCardClicked={activateExtensionsDetails}
              onDelete={toggleDeleteModal}
              onInstall={installById}
              onUpdate={updateById}
            />
          ) : null}
        </>
      ) : null}

      {entityDetails ? (
        <ExtensionDetailsPopup
          id={entityDetails.id}
          type={entityDetails.type}
          onCloseClicked={hideExtensionDetails}
          onDeleteClicked={toggleDeleteModal}
          onInstallClicked={installById}
          onUpdateClicked={updateById}
        />
      ) : null}

      {entityDeleting ? (
        <ExtensionDeletePopup
          deletingEntity={entityDeleting}
          onCancel={toggleDeleteModal}
          onConfirm={deleteById}
        />
      ) : null}
    </div>
  );
};

const ExtensionsRoute = (): JSX.Element => {
  const { getNavigation } = useNavigation();
  const { getExternalComponents } = useExternalComponents();

  const reloadNavigation = React.useCallback(() => {
    getNavigation();
    getExternalComponents();
  }, []);

  return <ExtensionsManager reloadNavigation={reloadNavigation} />;
};

export default ExtensionsRoute;
