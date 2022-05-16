import { useState, useEffect } from 'react';

import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router';
import { isEmpty, pick } from 'ramda';
import { useUpdateAtom, useAtomValue } from 'jotai/utils';

import { Typography } from '@mui/material';

import {
  getData,
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
import { Props, WizardButtonsTypes, Poller } from '../models';
import { pollersEndpoint, wizardFormEndpoint } from '../api/endpoints';

const RemoteServerWizardStepTwo = ({
  goToNextStep,
  goToPreviousStep,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const [pollers, setPollers] = useState<Array<Poller> | null>(null);

  const [linkedPollers, setLinkedPollers] = useState<Array<SelectEntry>>([]);

  const { sendRequest: getPollersRequest } = useRequest<{
    items: Array<Poller>;
  }>({
    request: getData,
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

  const filterOutDefaultPoller = (itemArr): Array<Poller> => {
    return itemArr.filter(({ id }) => id !== '1');
  };

  const getPollers = (): void => {
    getRPollersRequest({
      data: null,
      endpoint: pollersEndpoint,
    }).then(({ items }) => {
      setPollers(
        isEmpty(items)
          ? null
          : filterOutDefaultPoller(
              items.map(({ id, text }) => ({ id, name: text })),
            ),
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

  const pollersOptions = pollers?.map(
    pick(['id', 'name']),
  ) as Array<SelectEntry>;

  useEffect(() => {
    getPollers();
  }, []);

  return (
    <div>
      <div className={classes.formHeading}>
        <Typography variant="h6">
          {t(labelAdvancedServerConfiguration)}
        </Typography>
      </div>
      <form autoComplete="off" onSubmit={handleSubmit}>
        <MultiAutocompleteField
          fullWidth
          label={t(labelRemoteServers)}
          options={pollersOptions || []}
          value={linkedPollers}
          onChange={changeValue}
        />
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
