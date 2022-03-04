import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router';
import { isEmpty } from 'ramda';
import { useUpdateAtom, useAtomValue } from 'jotai/utils';

import { Typography, Button } from '@mui/material';

import {
  postData,
  useRequest,
  SelectEntry,
  MultiAutocompleteField,
} from '@centreon/ui';

import { useStyles } from '../../styles/partials/form/PollerWizardStyle';
import routeMap from '../../reactRoutes/routeMap';
import {
  remoteServerAtom,
  setRemoteServerWizardDerivedAtom,
} from '../PollerAtoms';

interface Props {
  goToNextStep: () => void;
  goToPreviousStep: () => void;
}
interface RemoteList {
  id: string;
  ip: string;
  name: string;
}

const getRemoteServersEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=getRemotesList';
const wizardFormEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer';

const FormRemoteServerStepTwo = ({
  goToNextStep,
  goToPreviousStep,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const [remoteServers, setRemoteServers] =
    React.useState<Array<RemoteList> | null>(null);

  const [linkedPollers, setLinkedPollers] = React.useState<Array<SelectEntry>>(
    [],
  );

  const { sendRequest: getRemoteServersRequest } = useRequest<
    Array<RemoteList>
  >({
    request: postData,
  });
  const { sendRequest: postWizardFormRequest } = useRequest<{
    s;
    success: boolean;
    task_id: number | string | null;
  }>({
    request: postData,
  });

  const pollerData = useAtomValue(remoteServerAtom);
  const setWizard = useUpdateAtom(setRemoteServerWizardDerivedAtom);

  const filterOutDefaultPoller = (itemArr): Array<RemoteList> => {
    for (let i = 0; i < itemArr.length; i += 1) {
      if (itemArr[i].id === '1') itemArr.splice(i, 1);
    }

    return itemArr;
  };

  const getRemoteServers = (): void => {
    getRemoteServersRequest({
      data: null,
      endpoint: getRemoteServersEndpoint,
    }).then((retrievedRemoteServers) => {
      setRemoteServers(
        isEmpty(retrievedRemoteServers)
          ? null
          : filterOutDefaultPoller(retrievedRemoteServers),
      );
    });
  };

  React.useEffect(() => {
    getRemoteServers();
  }, []);

  const navigate = useNavigate();

  const changeValue = (_, Pollers): void => {
    setLinkedPollers(Pollers);
  };

  const handleSubmit = (event): void => {
    event.preventDefault();
    const dataToPost = {
      ...pollerData,
      linked_pollers: linkedPollers.map(({ id }) => id),
    };
    dataToPost.server_type = 'remote';

    postWizardFormRequest({
      data: dataToPost,
      endpoint: wizardFormEndpoint,
    })
      .then(({ success, task_id }) => {
        if (success && task_id) {
          setWizard({
            submitStatus: success,
            taskId: task_id,
          });

          goToNextStep();
        } else {
          navigate(routeMap.pollerList);
        }
      })
      .catch(() => undefined);
  };

  return (
    <div>
      <div className={classes.formHeading}>
        <Typography variant="h6">
          {t('Add advanced server configuration')}
        </Typography>
      </div>
      <form autoComplete="off" onSubmit={handleSubmit}>
        {remoteServers && (
          <MultiAutocompleteField
            fullWidth
            label={t('Select pollers to be attached to this new Remote Server')}
            options={remoteServers.map((c) => ({
              id: c.id,
              name: c.name,
            }))}
            value={linkedPollers}
            onChange={changeValue}
          />
        )}
        <div className={classes.formButton}>
          <Button size="small" onClick={goToPreviousStep}>
            {t('Previous')}
          </Button>
          <Button
            color="primary"
            size="small"
            type="submit"
            variant="contained"
          >
            {t('Apply')}
          </Button>
        </div>
      </form>
    </div>
  );
};

export default FormRemoteServerStepTwo;
