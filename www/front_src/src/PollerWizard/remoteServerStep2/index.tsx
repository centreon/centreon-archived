import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router';
import { isEmpty } from 'ramda';
import { useUpdateAtom, useAtomValue } from 'jotai/utils';

import { Typography, Button, CircularProgress } from '@mui/material';

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
import {
  labelAdvancedServerConfiguration,
  labelRemoteServers,
  labelPrevious,
  labelApply,
} from '../translatedLabels';
import { Props, PollerOrRemoteList } from '../models';

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
  const [isLoading, setIsLoading] = React.useState<boolean>(false);

  const [remoteServers, setRemoteServers] =
    React.useState<Array<PollerOrRemoteList> | null>(null);

  const [linkedPollers, setLinkedPollers] = React.useState<Array<SelectEntry>>(
    [],
  );

  const { sendRequest: getRemoteServersRequest } = useRequest<
    Array<PollerOrRemoteList>
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

  const filterOutDefaultPoller = (itemArr): Array<PollerOrRemoteList> => {
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
    setIsLoading(true);

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
      .catch(() => {
        setIsLoading(false);
      });
  };

  const remoteServersOption = remoteServers?.map((c) => ({
    id: c.id,
    name: c.name,
  }));

  React.useEffect(() => {
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
        <div className={classes.formButton}>
          <Button size="small" onClick={goToPreviousStep}>
            {t(labelPrevious)}
          </Button>
          <Button
            color="primary"
            disabled={isLoading}
            endIcon={isLoading && <CircularProgress size={15} />}
            size="small"
            type="submit"
            variant="contained"
          >
            {t(labelApply)}
          </Button>
        </div>
      </form>
    </div>
  );
};

export default FormRemoteServerStepTwo;
