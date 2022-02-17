import * as React from 'react';

import Carousel from 'react-material-ui-carousel';
import { Responsive } from '@visx/visx';

import ChevronLeftIcon from '@mui/icons-material/ChevronLeft';
import ChevronRightIcon from '@mui/icons-material/ChevronRight';
import {
  Chip,
  Typography,
  Divider,
  Grid,
  Button,
  Link,
  CircularProgress,
} from '@mui/material';
import UpdateIcon from '@mui/icons-material/SystemUpdateAlt';
import DeleteIcon from '@mui/icons-material/Delete';
import InstallIcon from '@mui/icons-material/Add';

import { Dialog, IconButton, useRequest, getData } from '@centreon/ui';

import { Entity } from '../models';
import { buildEndPoint } from '../api/endpoint';

import {
  SliderSkeleton,
  HeaderSkeleton,
  ContentSkeleton,
  ReleaseNoteSkeleton,
} from './LoadingSkeleton';

interface Props {
  id: string;
  isLoading: boolean;
  onClose: () => void;
  onDelete: (id: string, type: string, description: string) => void;
  onInstall: (id: string, type: string) => void;
  onUpdate: (id: string, type: string) => void;
  type: string;
}

interface ExtensionDetails {
  result: Entity;
  status: boolean;
}

const ExtensionDetailPopup = ({
  id,
  type,
  onClose,
  onDelete,
  onInstall,
  onUpdate,
  isLoading,
}: Props): JSX.Element | null => {
  const [extensionDetails, setExtensionDetails] = React.useState<Entity | null>(
    null,
  );
  const [loading, setLoading] = React.useState<boolean>(true);

  const { sendRequest: sendExtensionDetailsValueRequests } =
    useRequest<ExtensionDetails>({
      request: getData,
    });

  React.useEffect(() => {
    sendExtensionDetailsValueRequests({
      endpoint: buildEndPoint({
        action: 'details',
        id,
        type,
      }),
    }).then((data) => {
      const { result } = data;
      if (result.images) {
        result.images = result.images.map((image) => {
          return `./${image}`;
        });
      }
      setExtensionDetails(result);
      setLoading(false);
    });
  }, [isLoading]);

  if (extensionDetails === null) {
    return null;
  }

  return (
    <Dialog open labelConfirm="Close" onClose={onClose} onConfirm={onClose}>
      <Grid container direction="column" spacing={2} style={{ width: 520 }}>
        <Grid item style={{ height: 300 }}>
          <Responsive.ParentSize>
            {({ width }): JSX.Element =>
              loading ? (
                <SliderSkeleton width={width} />
              ) : (
                <Carousel
                  fullHeightHover
                  NextIcon={<ChevronRightIcon />}
                  PrevIcon={<ChevronLeftIcon />}
                  animation="slide"
                  autoPlay={false}
                >
                  {extensionDetails.images?.map((image) => (
                    <img alt={image} key={image} src={image} width={width} />
                  ))}
                </Carousel>
              )
            }
          </Responsive.ParentSize>
        </Grid>
        <Grid item>
          {extensionDetails.version.installed &&
          extensionDetails.version.outdated ? (
            <IconButton
              size="large"
              onClick={(): void => {
                onUpdate(extensionDetails.id, extensionDetails.type);
              }}
            >
              <UpdateIcon />
            </IconButton>
          ) : null}
          {extensionDetails.version.installed ? (
            <Button
              color="primary"
              disabled={isLoading}
              endIcon={isLoading && <CircularProgress size={15} />}
              size="small"
              startIcon={<DeleteIcon />}
              variant="contained"
              onClick={(): void => {
                onDelete(
                  extensionDetails.id,
                  extensionDetails.type,
                  extensionDetails.title,
                );
              }}
            >
              Delete
            </Button>
          ) : (
            <Button
              color="primary"
              disabled={isLoading}
              endIcon={isLoading && <CircularProgress size={15} />}
              size="small"
              startIcon={<InstallIcon />}
              variant="contained"
              onClick={(): void => {
                onInstall(extensionDetails.id, extensionDetails.type);
              }}
            >
              Install
            </Button>
          )}
        </Grid>
        <Grid item>
          {loading ? (
            <HeaderSkeleton />
          ) : (
            <>
              <Typography variant="h5">{extensionDetails.title}</Typography>
              <Grid container spacing={1}>
                <Grid item>
                  <Chip
                    label={
                      (!extensionDetails.version.installed
                        ? 'Available '
                        : '') + extensionDetails.version.available
                    }
                  />
                </Grid>
                <Grid item>
                  <Chip label={extensionDetails.stability} />
                </Grid>
              </Grid>
            </>
          )}
        </Grid>
        <Grid item>
          {loading ? (
            <ContentSkeleton />
          ) : (
            <>
              {extensionDetails.last_update ? (
                <Typography variant="body1">{`Last update ${extensionDetails.last_update}`}</Typography>
              ) : null}
              <Typography variant="h6">Description</Typography>
              <Typography variant="body2">
                {extensionDetails.description}
              </Typography>
            </>
          )}
        </Grid>
        <Grid item>
          <Divider />
        </Grid>
        <Grid item>
          {loading ? (
            <ReleaseNoteSkeleton />
          ) : (
            <Link href={extensionDetails.release_note}>
              <Typography>{extensionDetails.release_note}</Typography>
            </Link>
          )}
        </Grid>
      </Grid>
    </Dialog>
  );
};

export default ExtensionDetailPopup;
