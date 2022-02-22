import * as React from 'react';

import { find, propEq, pathEq, filter, isEmpty } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import UpdateIcon from '@mui/icons-material/SystemUpdateAlt';
import InstallIcon from '@mui/icons-material/Add';
import Stack from '@mui/material/Stack';
import { Button } from '@mui/material';
import { makeStyles } from '@mui/styles';

import {
  useRequest,
  getData,
  postData,
  SelectEntry,
  useSnackbar,
} from '@centreon/ui';

import Hook from '../../components/Hook';
import useNavigation from '../../Navigation/useNavigation';
import useExternalComponents from '../../externalComponents/useExternalComponents';
import { appliedFilterCriteriasAtom } from '../Filter/filterAtoms';

import { deleteExtension } from './api';
import ExtensionsHolder from './ExtensionsHolder';
import ExtensionDetailsPopup from './ExtensionDetailsDialog';
import ExtensionDeletePopup from './ExtensionDeleteDialog';
import {
  Extensions,
  EntityType,
  ExtensionsStatus,
  ExtensionResult,
  InstallOrUpdateExtensionResult,
  EntityDeleting,
} from './models';
import { buildEndPoint, buildExtensionEndPoint } from './api/endpoint';

const useStyles = makeStyles((theme) => ({
  contentWrapper: {
    [theme.breakpoints.up(767)]: {
      padding: theme.spacing(1.5),
    },
    boxSizing: 'border-box',
    margin: theme.spacing(0, 'auto'),
    padding: theme.spacing(1.5, 2.5, 0, 2.5),
  },
}));

interface Props {
  reloadNavigation: () => void;
}

const ExtensionsManager = ({ reloadNavigation }: Props): JSX.Element => {
  const classes = useStyles();
  const { showErrorMessage, showSuccessMessage } = useSnackbar();

  const [extensions, setExtension] = React.useState<Extensions>({
    module: {
      entities: [],
    },
    widget: {
      entities: [],
    },
  });

  const [modulesActive, setModulesActive] = React.useState(false);
  const [widgetsActive, setWidgetsActive] = React.useState(false);

  const [entityDetails, setEntityDetails] = React.useState<EntityType | null>(
    null,
  );

  const [entityDeleting, setEntityDeleting] =
    React.useState<EntityDeleting | null>(null);

  const [extensionsInstallingStatus, setExtensionsInstallingStatus] =
    React.useState<ExtensionsStatus>({});

  const [extensionsUpdatingStatus, setExtensionsUpdatingStatus] =
    React.useState<ExtensionsStatus>({});

  const [confirmedDeletingEntityId, setConfirmedDeletingEntityId] =
    React.useState<string | null>(null);

  const { sendRequest: sendExtensionsRequests } = useRequest<ExtensionResult>({
    request: getData,
  });

  const { sendRequest: sendUpdateOrInstallExtensionRequests } =
    useRequest<InstallOrUpdateExtensionResult>({
      request: postData,
    });

  const { sendRequest: sendDeleteExtensionRequests } = useRequest({
    request: deleteExtension,
  });

  const getAppliedFilterCriteriasAtom = useAtomValue(
    appliedFilterCriteriasAtom,
  );

  React.useEffect(() => {
    const types = find(propEq('name', 'types'), getAppliedFilterCriteriasAtom);
    const statuses = find(
      propEq('name', 'statuses'),
      getAppliedFilterCriteriasAtom,
    );

    if (types && types.value) {
      const typesValues = types.value as Array<SelectEntry>;
      setModulesActive(!!find(propEq('id', 'MODULE'), typesValues));
      setWidgetsActive(!!find(propEq('id', 'WIDGET'), typesValues));
    }

    sendExtensionsRequests({
      endpoint: buildExtensionEndPoint({
        action: 'list',
        criteriaStatus: statuses,
      }),
    }).then(({ status, result }) => {
      if (status) {
        setExtension(result as Extensions);
      } else {
        showErrorMessage(result as string);
      }
    });
  }, [getAppliedFilterCriteriasAtom]);

  const getEntitiesByKeyAndVersionParam = (
    param,
    equals,
    key,
  ): Array<EntityType> => {
    const resArray: Array<EntityType> = [];
    if (extensions) {
      for (let i = 0; i < extensions[key].entities.length; i += 1) {
        const entity = extensions[key].entities[i];
        if (entity.version[param] === equals) {
          resArray.push({
            id: entity.id,
            type: key,
          });
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

  const updateAllEntities = (): void => {
    const entities = getAllEntitiesByVersionParam('outdated', true);
    if (entities.length <= 0 || !entities) {
      return;
    }
    entities.forEach((entity) => {
      updateById(entity.id, entity.type);
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

  const updateById = (id: string, type: string): void => {
    setExtensionsUpdatingStatusByIds(id, true);
    sendUpdateOrInstallExtensionRequests({
      endpoint: buildEndPoint({
        action: 'update',
        id,
        type,
      }),
    })
      .then(({ status, result }) => {
        if (!status) {
          showErrorMessage(result.message as string);
        } else {
          showSuccessMessage('successful Update');
        }

        return sendExtensionsRequests({
          endpoint: buildExtensionEndPoint({
            action: 'list',
            criteriaStatus: find(
              propEq('name', 'statuses'),
              getAppliedFilterCriteriasAtom,
            ),
          }),
        });
      })
      .then(({ status, result }) => {
        if (status) {
          setExtension(result as Extensions);
        }
        setExtensionsUpdatingStatusByIds(id, false);
        reloadNavigation();
      });
  };

  const installById = (id: string, type: string): void => {
    setExtensionsInstallingStatusByIds(id, true);
    sendUpdateOrInstallExtensionRequests({
      endpoint: buildEndPoint({
        action: 'install',
        id,
        type,
      }),
    })
      .then(({ status, result }) => {
        if (!status) {
          showErrorMessage(result.message as string);
        } else {
          showSuccessMessage('Successful Installation');
        }

        return sendExtensionsRequests({
          endpoint: buildExtensionEndPoint({
            action: 'list',
            criteriaStatus: find(
              propEq('name', 'statuses'),
              getAppliedFilterCriteriasAtom,
            ),
          }),
        });
      })
      .then(({ status, result }) => {
        if (status) {
          setExtension(result as Extensions);
        }
        setExtensionsInstallingStatusByIds(id, false);
        reloadNavigation();
      });
  };

  const setExtensionsUpdatingStatusByIds = (
    id: string,
    updating: boolean,
  ): void => {
    let statuses = extensionsUpdatingStatus;
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
    setEntityDeleting(null);
    sendDeleteExtensionRequests({
      id,
      type,
    })
      .then(({ status, result }) => {
        setConfirmedDeletingEntityId(null);
        if (!status) {
          showErrorMessage(result as string);
        } else {
          showSuccessMessage('Successful Deletion');
        }

        return sendExtensionsRequests({
          endpoint: buildExtensionEndPoint({
            action: 'list',
            criteriaStatus: find(
              propEq('name', 'statuses'),
              getAppliedFilterCriteriasAtom,
            ),
          }),
        });
      })
      .then(({ status, result }) => {
        if (status) {
          setExtension(result as Extensions);
        }
        reloadNavigation();
      });
  };

  const notInstallableExtensionModuleExiste = !isEmpty(
    filter(pathEq(['version', 'installed'], false), extensions.module.entities),
  );

  const notInstallableExtensionWidgetExiste = !isEmpty(
    filter(pathEq(['version', 'installed'], false), extensions.widget.entities),
  );

  const notUpdatableExtensionModuleExiste = !isEmpty(
    filter(pathEq(['version', 'outdated'], true), extensions.module.entities),
  );

  const notUpdatableExtensionWidgetExiste = !isEmpty(
    filter(pathEq(['version', 'outdated'], true), extensions.widget.entities),
  );

  const updatable =
    notUpdatableExtensionModuleExiste || notUpdatableExtensionWidgetExiste;

  const installable =
    notInstallableExtensionModuleExiste || notInstallableExtensionWidgetExiste;

  return (
    <div>
      <div className={classes.contentWrapper}>
        <Stack direction="row" spacing={2}>
          {updatable && (
            <Button
              color="primary"
              size="small"
              startIcon={<UpdateIcon />}
              variant="contained"
              onClick={updateAllEntities}
            >
              update all
            </Button>
          )}

          {installable && (
            <Button
              color="primary"
              size="small"
              startIcon={<InstallIcon />}
              // style={{ marginLeft: 8, marginRight: 8 }}
              variant="contained"
              onClick={installAllEntities}
            >
              install all
            </Button>
          )}
          <Hook path="/administration/extensions/manager" />
        </Stack>
      </div>
      {extensions && (
        <>
          {extensions.module &&
            (modulesActive || (!modulesActive && !widgetsActive)) && (
              <ExtensionsHolder
                deletingEntityId={confirmedDeletingEntityId}
                entities={extensions.module.entities}
                installing={extensionsInstallingStatus}
                title="Modules"
                type="module"
                updating={extensionsUpdatingStatus}
                onCard={activateExtensionsDetails}
                onDelete={toggleDeleteModal}
                onInstall={installById}
                onUpdate={updateById}
              />
            )}

          {extensions.widget &&
            (widgetsActive || (!modulesActive && !widgetsActive)) && (
              <ExtensionsHolder
                deletingEntityId={confirmedDeletingEntityId}
                entities={extensions.widget.entities}
                installing={extensionsInstallingStatus}
                title="Widgets"
                type="widget"
                updating={extensionsUpdatingStatus}
                onCard={activateExtensionsDetails}
                onDelete={toggleDeleteModal}
                onInstall={installById}
                onUpdate={updateById}
              />
            )}
        </>
      )}

      {entityDetails && (
        <ExtensionDetailsPopup
          id={entityDetails.id}
          isLoading={
            extensionsInstallingStatus[entityDetails.id] ||
            extensionsUpdatingStatus[entityDetails.id] ||
            confirmedDeletingEntityId === entityDetails.id
          }
          type={entityDetails.type}
          onClose={hideExtensionDetails}
          onDelete={toggleDeleteModal}
          onInstall={installById}
          onUpdate={updateById}
        />
      )}

      {entityDeleting && (
        <ExtensionDeletePopup
          deletingEntity={entityDeleting}
          onCancel={toggleDeleteModal}
          onConfirm={deleteById}
        />
      )}
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
