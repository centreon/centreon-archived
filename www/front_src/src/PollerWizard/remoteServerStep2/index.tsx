import { useState, useEffect } from 'react';

import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router';
import { isEmpty, pick } from 'ramda';
import { useUpdateAtom, useAtomValue } from 'jotai/utils';

import { Typography } from '@mui/material';

import {
  postData,
  useRequest,
  SelectEntry,
  MultiAutocompleteField,
} from '@centreon/ui';

import WizardButtons from '../forms/wizardButtons';
import { useStyles } from '../../styles/partials/form/PollerWizardStyle';
import routeMap from '../../reactRoutes/routeMap';
import {
  remoteServerAtom,
  setRemoteServerWizardDerivedAtom,
} from '../pollerAtoms';
import {
  labelAdvancedServerConfiguration,
  labelRemoteServers,
} from '../translatedLabels';
import { Props, PollerRemoteList, WizardButtonsTypes } from '../models';

const getRemoteServersEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=getRemotesList';
const wizardFormEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer';

const RemoteServerWizardStepTwo = ({
  goToNextStep,
  goToPreviousStep,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const [remoteServers, setRemoteServers] =
    React.useState<Array<PollerRemoteList> | null>(null);

  const [linkedPollers, setLinkedPollers] = useState<Array<SelectEntry>>([]);

  const { sendRequest: getRemoteServersRequest } = useRequest<
    Array<PollerRemoteList>
  >({
    request: postData,
  });
  const { sendRequest: postWizardFormRequest, sending: loading } = useRequest<{
    s;
    success: boolean;
    task_id: number | string | null;
  }>({
    request: postData,
  });

  const pollerData = useAtomValue(remoteServerAtom);
  const setWizard = useUpdateAtom(setRemoteServerWizardDerivedAtom);

  const filterOutDefaultPoller = (itemArr): Array<PollerRemoteList> => {
    return itemArr.filter(({ id }) => id !== '1');
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

  const remoteServersOption = remoteServers?.map(pick(['id', 'name']));

  useEffect(() => {
    getRemoteServers();
  }, []);

  return (
    <div>
      <div className={classes.formHeading}>
        <Typography variant="h6">
          {t(labelAdvancedServerConfiguration)}
        </Typography>
      </div>
      <form autoComplete="off" onSubmit={handleSubmit}>
        {remoteServersOption && (
          <MultiAutocompleteField
            fullWidth
            label={t(labelRemoteServers)}
            options={remoteServersOption}
            value={linkedPollers}
            onChange={changeValue}
          />
        )}
        <WizardButtons
          disabled={loading}
          goToPreviousStep={goToPreviousStep}
          type={WizardButtonsTypes.Apply}
        />
      </form>
    </div>
  );
};

export default RemoteServerWizardStepTwo;
